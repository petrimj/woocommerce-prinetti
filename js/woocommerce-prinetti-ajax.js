jQuery(document).ready(function($) {
	$('#pmj_prinetti_submit').click(function() {
		$('#pmj_prinetti_loading').show();
		$('#pmj_prinetti_submit').attr('disabled', true);
		var abort = false;
		$('.prinetti_input_error').removeClass('prinetti_input_error');
		$('input.required').each(function() {
			if ($(this).val() === '') {
				$(this).addClass('prinetti_input_error');
				abort = true;
			}
		});
		if (abort) {
			$('#pmj_prinetti_loading').hide();
			$('#pmj_prinetti_submit').attr('disabled', false);
			return false;
		} else {
			var pmj_prinetti_data = $('form').serialize();
			var data = {
				action: 'pmj_get_results',
				pmj_nonce: pmj_vars.pmj_nonce,
				pmj_data: pmj_prinetti_data
			};
			$.post(ajaxurl, data, function(response) {
				$('#results').html(response);
				$('#pmj_prinetti_loading').hide();
				$('#pmj_prinetti_submit').attr('disabled', false);
				refresh();
			});

			function refresh() {
				var data2 = {
					action: 'refresh_created_labels_ajax',
					pmj_nonce: pmj_vars.pmj_nonce,
					pmj_data: pmj_prinetti_data
				};
				$.post(ajaxurl, data2, function(result) {
					$('#created_labels').html(result);
				});
				return false;
			}
		}
	});
	$('#pe_checkbox').change(function() {
		if (this.checked) {
			$('#pe_lisatiedot').fadeIn('normal');
		} else {
			$('#pe_lisatiedot').fadeOut('normal');
		}
	});
	
	$('#mp_checkbox').change(function() {
		if (this.checked) {
			$('#mp_lisatiedot').fadeIn('normal');
		} else {
			$('#mp_lisatiedot').fadeOut('normal');
		}
	});
	
	$('#erilliskasiteltava_checkbox').change(function() {
		if (this.checked) {
			$('#erilliskasiteltava_lisatiedot').fadeIn('normal');
		} else {
			$('#erilliskasiteltava_lisatiedot').fadeOut('normal');
		}
	});
});