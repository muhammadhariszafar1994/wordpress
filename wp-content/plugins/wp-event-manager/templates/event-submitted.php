<?php
global $wp_post_types;

switch($event->post_status) :
	case 'publish' :
		// translators: %1$s is the singular name of the listing (e.g., "Event"), and %2$s is the URL to view the listing.
		printf(
			'<p class="post-submitted-success-green-message wpem-alert wpem-alert-success">' .
			esc_html('%1$s listed successfully. To view your listing <a href="%2$s">click here</a>.', 'wp-event-manager') .
			'</p>',
			esc_attr($wp_post_types['event_listing']->labels->singular_name),
			esc_url(get_permalink($event->ID))
		);
		break;
		case 'pending':
			$event_singular = esc_attr($wp_post_types['event_listing']->labels->singular_name);
			$custom_message = get_option('wpem_event_submit_success_message');
			if (empty($custom_message)) {
				$custom_message = __('%s submitted successfully. Your listing will be visible once approved.', 'wp-event-manager');
			}
			$formatted_message = sprintf($custom_message, $event_singular);
			echo '<p class="post-submitted-success-green-message wpem-alert wpem-alert-success">' . wp_kses_post($formatted_message) . '</p>';
			break;
	default :
		do_action('event_manager_event_submitted_content_' . str_replace('-', '_', sanitize_title($event->post_status)), $event);
		break;

endswitch;

do_action('event_manager_event_submitted_content_after', sanitize_title($event->post_status), $event);