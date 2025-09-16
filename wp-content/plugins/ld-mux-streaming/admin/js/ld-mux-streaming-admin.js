(function( $ ) {
	// 'use strict';

	jQuery(document).ready(function ($) {
		$('#ld-mux-upload-form').on('submit', function (e) {
			e.preventDefault();

			const form = e.target;
			const formData = new FormData();
			const file = form.video_file.files[0];

			formData.append('action', 'ld_mux_upload_video');
			formData.append('ld_mux_upload_video_nonce_field', form.video_nonce_field.value);
			formData.append('ld_mux_video_title', form.video_title.value);
			formData.append('ld_mux_video_desc', form.video_description.value);

			if(file) formData.append('ld_mux_video_file', form.video_file.files[0]);

			$('#ld-mux-upload-response').html('<p>Uploading... Please wait.</p>');

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function (res) {
					if (res.success) {
						$('#ld-mux-upload-response').html('<p style="color:green;">' + res.data.message + '</p>');
					} else {
						$('#ld-mux-upload-response').html('<p style="color:red;">Error: ' + (res.data?.message || 'Unknown error') + '</p>');
					}
				},
				error: function (xhr) {
					$('#ld-mux-upload-response').html('<p style="color:red;">Upload failed: ' + xhr.status + ' ' + xhr.statusText + '</p>');
				}
			});
		});
	});

})( jQuery );