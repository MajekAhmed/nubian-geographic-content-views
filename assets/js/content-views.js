/**
 * NGCV content views — progressive enhancement.
 *
 * Vanilla JS, no dependencies, namespace: NGCV. Everything it touches is
 * already server-rendered and fully functional without this script; each part
 * returns immediately when its markup is absent, so the same file serves both
 * contexts without loading dead code paths.
 *
 * Enhancements only:
 *   1. TOC collapse toggle (aria-expanded; the list is open by default).
 *   2. TOC scroll-spy (highlights the current heading) via ONE
 *      IntersectionObserver.
 *   3. Smooth in-page scrolling for TOC links (skipped under reduced motion).
 *   4. Article sharing: copy-link + native Web Share.
 *
 * Entrance motion is NOT here: it is a pure CSS keyframe in
 * components/layout.css, so it behaves identically with or without this file.
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
			this.share();
		},

		/**
		 * Article sharing: copy-link + native Web Share (progressive).
		 */
		share: function () {
			var block = document.querySelector('[data-ngcv-share]');
			if (!block) {
				return;
			}

			var url = block.getAttribute('data-ngcv-share-url') || window.location.href;
			var title = block.getAttribute('data-ngcv-share-title') || document.title;

			var nativeItem = block.querySelector('[data-ngcv-native]');
			var nativeButton = block.querySelector('[data-ngcv-native-button]');
			if (nativeItem && nativeButton && typeof navigator.share === 'function') {
				nativeItem.removeAttribute('hidden');
				nativeButton.addEventListener('click', function () {
					navigator.share({ title: title, url: url }).catch(function () {});
				});
			}

			var copyButton = block.querySelector('[data-ngcv-copy]');
			var status = block.querySelector('[data-ngcv-copy-status]');
			if (!copyButton) {
				return;
			}

			var done = function (message) {
				if (status) {
					status.textContent = message;
				}
			};

			copyButton.addEventListener('click', function () {
				var fallback = function () {
					var input = document.createElement('input');
					input.value = url;
					input.setAttribute('readonly', 'readonly');
					input.style.position = 'absolute';
					input.style.opacity = '0';
					document.body.appendChild(input);
					input.select();
					try {
						document.execCommand('copy');
						done(copyButton.getAttribute('data-ngcv-copied') || 'Copied');
					} catch (error) {
						done(copyButton.getAttribute('data-ngcv-failed') || 'Copy failed');
					}
					document.body.removeChild(input);
				};

				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(url).then(
						function () {
							done(copyButton.getAttribute('data-ngcv-copied') || 'Copied');
						},
						fallback
					);
				} else {
					fallback();
				}
			});
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
