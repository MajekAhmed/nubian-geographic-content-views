/**
 * NGCV content views — progressive enhancement.
 *
 * Vanilla JS, no dependencies, namespace: NGCV. The article body and the
 * table of contents are fully server-rendered and functional without this
 * script. Enhancements only:
 *   1. TOC collapse toggle (aria-expanded; list is open by default).
 *   2. TOC scroll-spy via IntersectionObserver (highlights current heading).
 *   3. Smooth in-page scrolling for TOC links (skipped under reduced motion).
 */
(function () {
	'use strict';

	if (window.NGCV) {
		return;
	}

	var NGCV = {
		/**
		 * Entry point.
		 */
		init: function () {
			this.toc();
		},

		/**
		 * Table of contents enhancements.
		 */
		toc: function () {
			var nav = document.querySelector('.ngcv-toc');
			if (!nav) {
				return;
			}

			var toggle = nav.querySelector('.ngcv-toc-toggle');
			var list = nav.querySelector('.ngcv-toc-list');

			if (toggle && list) {
				toggle.addEventListener('click', function () {
					var expanded = toggle.getAttribute('aria-expanded') === 'true';
					toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
					if (expanded) {
						list.setAttribute('hidden', 'hidden');
					} else {
						list.removeAttribute('hidden');
					}
				});
			}

			if (!list) {
				return;
			}

			var links = Array.prototype.slice.call(list.querySelectorAll('a[href^="#"]'));
			if (!links.length || typeof window.IntersectionObserver !== 'function') {
				return;
			}

			var map = {};
			links.forEach(function (link) {
				var id = (link.getAttribute('href') || '').slice(1);
				var target = id ? document.getElementById(id) : null;
				if (target) {
					map[id] = link;
				}
			});

			var setActive = function (id) {
				links.forEach(function (link) {
					link.classList.remove('is-active');
					link.removeAttribute('aria-current');
				});
				var active = map[id];
				if (active) {
					active.classList.add('is-active');
					active.setAttribute('aria-current', 'location');
				}
			};

			var observer = new IntersectionObserver(
				function (entries) {
					entries.forEach(function (entry) {
						if (entry.isIntersecting) {
							setActive(entry.target.id);
						}
					});
				},
				{ rootMargin: '-15% 0px -70% 0px', threshold: 0 }
			);

			Object.keys(map).forEach(function (id) {
				var target = document.getElementById(id);
				if (target) {
					observer.observe(target);
				}
			});

			nav.addEventListener('click', function (event) {
				var link = event.target && event.target.closest
					? event.target.closest('a[href^="#"]')
					: null;
				if (!link || !nav.contains(link)) {
					return;
				}
				var target = document.getElementById((link.getAttribute('href') || '').slice(1));
				if (!target) {
					return;
				}
				var reduce = window.matchMedia
					&& window.matchMedia('(prefers-reduced-motion: reduce)').matches;
				if (reduce) {
					return; // Native jump (instant), fully accessible.
				}
				event.preventDefault();
				target.scrollIntoView({ behavior: 'smooth', block: 'start' });
				if (window.history && window.history.pushState) {
					window.history.pushState(null, '', link.getAttribute('href'));
				}
			});
		}
	};

	window.NGCV = NGCV;

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			NGCV.init();
		});
	} else {
		NGCV.init();
	}
})();
