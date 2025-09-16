(function( $ ) {
	'use strict';

	jQuery(document).ready(function($) {
		$('.view-statistics').on('click', function(e) {
			e.preventDefault();

			const lessonId = $(this).data('lesson');
			const userId   = $(this).data('user');

			$('#statistic-modal').show();
			$('#statistic-modal-content').html('Loading...');

			$.post(window.PlantActivityStats.ajax_url, {
				action: 'get_plant_activity_statistics',
				_wpnonce: window.PlantActivityStats.nonce,
				lesson_id: lessonId,
				user_id: userId
			}, function(response) {
				if (response.success) {
					let html = '<table class="widefat striped"><tbody>';
					$.each(response.data.stats, function(key, value) {
						html += '<tr><td><strong>' + key.replace(/_/g, ' ') + '</strong></td><td>' + value + '</td></tr>';
					});
					html += '</tbody></table>';
					$('#statistic-modal-content').html(html);
				} else {
					$('#statistic-modal-content').html('<p>' + response.data.message + '</p>');
				}
			});
		});

		$('#statistic-modal-close, #statistic-modal-overlay').on('click', function() {
			$('#statistic-modal').hide();
		});
	});

})( jQuery );