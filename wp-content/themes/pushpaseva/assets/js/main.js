(function () {
	'use strict';

	// Smooth scroll for in-page anchor links.
	document.querySelectorAll('a[href^="#"]').forEach(function (link) {
		link.addEventListener('click', function (e) {
			var target = document.querySelector(link.getAttribute('href'));
			if (target) {
				e.preventDefault();
				target.scrollIntoView({ behavior: 'smooth', block: 'start' });
			}
		});
	});

	// Delivery area check — captures the value only, no real serviceability check yet.
	var form = document.getElementById('ps-delivery-form');
	if (form) {
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var input = document.getElementById('ps-delivery-input');
			var result = document.getElementById('ps-delivery-result');
			if (!input.value.trim()) {
				return;
			}
			result.hidden = false;
			result.textContent = 'Great news! We deliver to "' + input.value.trim() + '". Choose a package below to get started.';
			var packages = document.getElementById('packages');
			if (packages) {
				setTimeout(function () {
					packages.scrollIntoView({ behavior: 'smooth', block: 'start' });
				}, 600);
			}
		});
	}
})();
