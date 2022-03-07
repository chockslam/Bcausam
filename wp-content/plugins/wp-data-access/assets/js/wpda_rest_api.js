const WPDA_REST_API_PATH = 'wpda/v1/';

function wpda_rest_api(path, data) {
	jQuery.ajax({
		url: wpApiSettings.root + WPDA_REST_API_PATH + path,
		method: "POST",
		beforeSend: function (xhr) {
			xhr.setRequestHeader("X-WP-Nonce", wpApiSettings.nonce);
		},
		data: data
	}).done(function(response) {
		console.log(response);
	});
}