<?php
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

class Prinetti_Shipment {

    protected $options;
    protected $tilaus;
    protected $result;

    // Routing segment
    protected $prinetti_secret_key;
    protected $prinetti_order_id;
    protected $prinetti_tracking_code;
    protected $prinetti_routing_target;
    protected $prinetti_routing_source;
    protected $prinetti_routing_account;
    protected $prinetti_routing_key;
    protected $prinetti_routing_id;
    protected $prinetti_routing_mode;

    // Additional services
    protected $prinetti_shipment_sender_bic;
    protected $prinetti_shipment_sender_iban;

    // Variables for sender
    protected $prinetti_shipment_sender_name1;
    protected $prinetti_shipment_sender_name2;
    protected $prinetti_shipment_sender_addr1;
    protected $prinetti_shipment_sender_addr2;
    protected $prinetti_shipment_sender_postcode;
    protected $prinetti_shipment_sender_city;
    protected $prinetti_shipment_sender_country = 'FI';

    protected $prinetti_recipient_name1;
    protected $prinetti_recipient_name2;
    protected $prinetti_recipient_addr1;
    protected $prinetti_recipient_addr2;
    protected $prinetti_recipient_postcode;
    protected $prinetti_recipient_city;
    protected $prinetti_recipient_country = 'FI';
    protected $prinetti_recipient_phone;
    protected $prinetti_recipient_email;

    protected $prinetti_consignment_parcel_packagetype;
    protected $prinetti_consignment_product;
    protected $postiennakko;
    protected $prinetti_postiennakko_amount;
    protected $monipakettilahetys_count;
    protected $erilliskasiteltava_count;
    protected $xml_data;

    public function __construct($params, $options) {
        $this->prinetti_secret_key = $options['secret_key'];
        $this->prinetti_order_id = $params['post_ID'];
        $this->prinetti_routing_target = 1;
        $this->prinetti_routing_source = $options['routing_source']; //197
        $this->prinetti_routing_account = (int)$options['routing_account']; // 812702;
        $this->prinetti_routing_key = md5($this->prinetti_routing_account . $this->prinetti_order_id . $this->prinetti_secret_key);
        $this->prinetti_routing_id = $this->prinetti_order_id;
        $options['testmode'] == 'yes' ? $this->prinetti_routing_mode = 1 : $this->prinetti_routing_mode = 0; // Testitarkoituksessa 1, muuten 0
        $this->prinetti_shipment_sender_bic = $options['lahettaja_bic'];
        $this->prinetti_shipment_sender_iban = $options['lahettaja_iban'];

        $this->prinetti_shipment_sender_name1 = $options['lahettaja_nimi_1'];
        $this->prinetti_shipment_sender_name2 = $options['lahettaja_nimi_2'];
        $this->prinetti_shipment_sender_addr1 = $options['lahettaja_osoiterivi_1'];
        $this->prinetti_shipment_sender_addr2 = $options['lahettaja_osoiterivi_2'];
        $this->prinetti_shipment_sender_postcode = $options['lahettaja_postinumero'];
        $this->prinetti_shipment_sender_city = $options['lahettaja_toimipaikka'];
        $this->prinetti_shipment_sender_country = 'FI';

        $this->tilaus = new WC_order($params['post_ID']);

        $this->prinetti_recipient_name1 = $params['prinetti_vastaanottaja'];
        $this->prinetti_recipient_name2 = $params['prinetti_vastaanottaja_yritys']; // EI VIELÄ XML:ssä
        $this->prinetti_recipient_addr1 = $params['prinetti_osoite1'];
        $this->prinetti_recipient_addr2 = $params['prinetti_osoite2'];
        $this->prinetti_recipient_postcode = $params['prinetti_postinumero'];
        $this->prinetti_recipient_city = $params['prinetti_postitoimipaikka'];
        $this->prinetti_recipient_country = 'FI';
        $this->prinetti_recipient_phone = $params['prinetti_puhelinnumero'];
        $this->prinetti_recipient_email = $params['prinetti_email'];

        $this->prinetti_consignment_parcel_packagetype = 'PC';

        if (isset($params['palvelu']) ? $this->prinetti_consignment_product = $params['palvelu'] : $this->prinetti_consignment_product = '') ;

        // Cash on delivery
        if (isset($params['postiennakko']) && $params['postiennakko'] == 3101) {
            $this->postiennakko = true;
            $this->prinetti_postiennakko_amount = $params['postiennakko_summa'];
        } else {
            $this->postiennakko = false;
        }

        if (isset($params['monipaketti']) && $params['monipaketti'] == 3102) {
            $this->monipakettilahetys_count = $params['mp_count'];
        } else {
            $this->monipakettilahetys_count = 1;
        }

        // Erilliskäsiteltävä
        if (isset($params['erilliskasiteltava']) && $params['erilliskasiteltava'] == 3104) {
            $this->erilliskasiteltava_count = $params['er_count'];
        } else {
            $this->erilliskasiteltava_count = 0;
        }

    }


    /**
     * Generates reference number for invoice
     * @param $orderid
     * @return string
     */
    protected function viitenumero($orderid) {
        $orderid = strval($orderid);
        $paino = array(7, 3, 1);
        $summa = 0;
        for ($i = strlen($orderid) - 1, $j = 0; $i >= 0; $i--, $j++) {
            $summa += (int)$orderid[$i] * (int)$paino[$j % 3];
        }
        $tarkiste = (10 - ($summa % 10)) % 10;
        return $orderid . $tarkiste;
    }

    public function createXML() {
        $xml = new XMLWriter();
        $xml->openMemory();

        $xml->startDocument('1.0" encoding="UTF-8');
        $xml->startElement('eChannel'); //
        $xml->startElement('ROUTING');
        $xml->writeElement('Routing.Target', $this->prinetti_routing_target); // Kohdejärjestelmän tunnus
        $xml->writeElement('Routing.Source', $this->prinetti_routing_source); // Lähdejärjestelmän tunnus
        $xml->writeElement('Routing.Account', $this->prinetti_routing_account); // Lähettäjän yksilöivä tunnus
        $xml->writeElement('Routing.Key', $this->prinetti_routing_key); // Muodostetaan salaisen avaimen ja sanoman tarkistussumman perusteella
        $xml->writeElement('Routing.Id', $this->prinetti_routing_id); // Lähettäjän tilausnumero
        $xml->writeElement('Routing.Mode', $this->prinetti_routing_mode);
        $xml->endElement(); // ROUTING

        $xml->startElement('Shipment'); //
        $xml->startElement('Shipment.Sender'); // Lähettäjän tiedot
        $xml->writeElement('Sender.Name1', $this->prinetti_shipment_sender_name1); // Lähettäjän nimi
        $xml->writeElement('Sender.Name2', $this->prinetti_shipment_sender_name2); // Lähettäjän nimi2
        $xml->writeElement('Sender.Addr1', $this->prinetti_shipment_sender_addr1); // Lähettäjän katuosoite
        $xml->writeElement('Sender.Addr2', $this->prinetti_shipment_sender_addr2); // Lähettäjän osoite 2
        $xml->writeElement('Sender.Postcode', $this->prinetti_shipment_sender_postcode); // Lähtöpostinumero
        $xml->writeElement('Sender.City', $this->prinetti_shipment_sender_city); // Lähetyspaikka
        $xml->writeElement('Sender.Country', $this->prinetti_shipment_sender_country); // Lähettäjän maakoodi
        $xml->endElement(); // Shipment.Sender

        $xml->startElement('Shipment.Recipient');
        $xml->writeElement('Recipient.Name1', $this->prinetti_recipient_name1);
        $xml->writeElement('Recipient.Name2', $this->prinetti_recipient_name2);
        $xml->writeElement('Recipient.Addr1', $this->prinetti_recipient_addr1);
        $xml->writeElement('Recipient.Addr2', $this->prinetti_recipient_addr2);
        $xml->writeElement('Recipient.Postcode', $this->prinetti_recipient_postcode);
        $xml->writeElement('Recipient.City', $this->prinetti_recipient_city);
        $xml->writeElement('Recipient.Country', $this->prinetti_recipient_country);
        $xml->writeElement('Recipient.Phone', $this->prinetti_recipient_phone);
        $xml->writeElement('Recipient.Email', $this->prinetti_recipient_email);
        $xml->endElement(); // Shipment.Recipient

        $xml->startElement('Shipment.Consignment');
        //$xml->writeElement('Consignment.Reference', $prinetti_order_id); // Tämä tulee alas lisätietokenttään??
        $xml->writeElement('Consignment.Product', $this->prinetti_consignment_product);


        // Additional services
        if ($this->postiennakko === true) {
            $xml->startElement('Consignment.AdditionalService');
            $xml->writeElement('AdditionalService.ServiceCode', 3101);

            $xml->startElement('AdditionalService.Specifier');
            $xml->startAttribute('name');
            $xml->text('amount');
            $xml->endAttribute();
            $xml->text($this->prinetti_postiennakko_amount); // Veloitettava summa
            $xml->endElement();

            $xml->startElement('AdditionalService.Specifier');
            $xml->startAttribute('name');
            $xml->text('account');
            $xml->endAttribute();
            $xml->text($this->prinetti_shipment_sender_iban);
            $xml->endElement();

            $xml->startElement('AdditionalService.Specifier');
            $xml->startAttribute('name');
            $xml->text('reference');
            $xml->endAttribute();
            $xml->text($this->viitenumero($this->prinetti_order_id)); // Luodaan viitenumero
            $xml->endElement();

            $xml->startElement('AdditionalService.Specifier');
            $xml->startAttribute('name');
            $xml->text('codbic');
            $xml->endAttribute();
            $xml->text($this->prinetti_shipment_sender_bic);
            $xml->endElement();
            $xml->endElement(); // Consignment.AdditionalService
        }


        if ($this->monipakettilahetys_count > 1) {
            $xml->startElement('Consignment.AdditionalService'); //
            $xml->writeElement('AdditionalService.ServiceCode', 3102); // Monipaketti = 3102
            $xml->startElement('AdditionalService.Specifier');
            $xml->startAttribute('name');
            $xml->text('count');
            $xml->endAttribute();
            $xml->text($this->monipakettilahetys_count);
            $xml->endElement();
            $xml->endElement(); // Consignment.AdditionalService


            /*if ($this->erilliskasiteltava_count > 0)
                {
                    $xml->startElement('Consignment.AdditionalService'); //
                    $xml->writeElement('AdditionalService.ServiceCode', 3104);

                    $xml->endElement();
                }*/
        }


        for ($i = 0; $i < $this->monipakettilahetys_count; $i++) {
            $xml->startElement('Consignment.Parcel'); //
            $xml->writeAttribute('type', 'normal');

            $xml->writeElement('Parcel.Packagetype', "PC"); // Kollilaji


            if ($this->erilliskasiteltava_count > 0) {
                $xml->startElement('Parcel.ParcelService');
                $xml->writeElement('ParcelService.Servicecode', 3104);
                $xml->endElement(); // Parcel.ParcelService
                $this->erilliskasiteltava_count--;
            }


            $xml->endElement(); // Consignment.Parcel
        }


        $xml->endElement(); // Consignment
        $xml->endElement(); // Shipment
        $xml->endElement(); // eChannel

        $this->xml_data = $xml->flush();
        $xml->endDocument();
    }

    /**
     * Sends the xml generated with createXML()
     *
     * @return string
     */
    public function sendXML() {
        $ch = curl_init();

        file_put_contents("rivit.txt", $this->xml_data);

        $curl_url = 'https://echannel.prinetti.net/import.php';

        $ch = curl_init($curl_url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->xml_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $this->result = curl_exec($ch);

        curl_close($ch);

        $doc = new DOMDocument("1.0", "ISO-8859-1");
        $doc->loadXml($this->result);
        $response_status = $doc->getElementsByTagName('response.status')->item(0)->nodeValue;

        $dod = new DOMDocument("1.0", "ISO-8859-1");
        $dod->loadXml($this->result);
        $response_message = $dod->getElementsByTagName('response.message')->item(0)->nodeValue;

        if ($response_status != 0) {

            $error = [];
            $error['error_status'] = $response_status;
            $error['error_message'] = $response_message;
            return $error;


        } else {
            return $this->getResult();
        }
    }


    /**
     * Handles the return message from sendXML() and
     * returns tracking code
     *
     * @return string
     */
    private function getResult() {
        $dom = new DOMDocument("1.0", "ISO-8859-1");
        $dom->loadXml($this->result);
        $prinetti_tracking_code = $dom->getElementsByTagName('response.trackingcode')->item(0)->nodeValue;
        $this->prinetti_tracking_code = $prinetti_tracking_code;
        return $prinetti_tracking_code;
    }

    /**
     * Returns a link to the label generated with createXML & sendXML
     * @return string
     */
    public function getLabel() {


        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0" encoding="ISO-8859-1');
        $xml->startElement('eChannel'); //
        $xml->startElement('ROUTING');
        $xml->writeElement('Routing.Target', $this->prinetti_routing_target); // Kohdejärjestelmän tunnus
        $xml->writeElement('Routing.Source', $this->prinetti_routing_source); // Lähdejärjestelmän tunnus
        $xml->writeElement('Routing.Account', $this->prinetti_routing_account); // Lähettäjän yksilöivä tunnus
        $xml->writeElement('Routing.Key', $this->prinetti_routing_key); // Muodostetaan salaisen avaimen ja sanoman tarkistussumman perusteella
        $xml->writeElement('Routing.Id', $this->prinetti_routing_id); // Lähettäjän tilausnumero
        $xml->writeElement('Routing.Mode', $this->prinetti_routing_mode);
        $xml->endElement(); // ROUTING PÄÄTTYY

        $xml->startElement('PrintLabel');
        $xml->writeAttribute('responseFormat', 'link');
        $xml->writeElement('TrackingCode', $this->prinetti_tracking_code);

        $xml->endElement(); // END PrintLabel
        $xml->endElement(); // END eChannel
        $this->xml_data = $xml->flush();
        $xml->endDocument();

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://echannel.prinetti.net/returnPdf.php");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->xml_data);

        $result = curl_exec($ch);


        $dom = new DOMDocument();
        $dom->loadXml($result);

        $link = $dom->getElementsByTagName('response.link')->item(0)->nodeValue;

        return '<a target="_blank" href="' . $link . '">' . $this->prinetti_tracking_code . '</a>';

    }
}