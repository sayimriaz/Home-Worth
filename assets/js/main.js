(function () {
  var header = document.querySelector(".site-header");
  var toggle = document.querySelector(".nav-toggle");
  var nav = document.getElementById("site-nav");

  document.querySelectorAll(".lead-form, .contact-form").forEach(function (form) {
    var proof = form.querySelector('input[name="form_js"]');
    if (!proof) {
      return;
    }
    window.setTimeout(function () {
      proof.value = "ok";
    }, 600);
  });

  if (header) {
    var onScroll = function () {
      header.classList.toggle("is-scrolled", window.scrollY > 8);
    };
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
  }

  if (toggle && header && nav) {
    toggle.addEventListener("click", function () {
      var open = header.classList.toggle("is-open");
      toggle.setAttribute("aria-expanded", open ? "true" : "false");
      toggle.setAttribute("aria-label", open ? "Close menu" : "Open menu");
    });

    nav.querySelectorAll("a").forEach(function (link) {
      link.addEventListener("click", function () {
        header.classList.remove("is-open");
        toggle.setAttribute("aria-expanded", "false");
      });
    });
  }

  var revealNodes = document.querySelectorAll([
    ".about-top > *",
    ".about-bottom > *",
    ".why-grid > *",
    ".stats-grid > *",
    ".process > .container > h2",
    ".process-lead",
    ".step",
    ".testimonial-head",
    ".testimonial-wrap",
    ".faq-grid > *",
    ".cta-inner",
    ".contact-intro",
    ".contact-shell",
    ".privacy-wrap",
    ".thanks-card",
    ".footer-grid > *"
  ].join(","));

  if (revealNodes.length) {
    var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    revealNodes.forEach(function (el) {
      el.classList.add("reveal");
    });
    if (reduceMotion) {
      revealNodes.forEach(function (el) {
        el.classList.add("is-in");
      });
    } else {
      var parents = [];
      revealNodes.forEach(function (el) {
        if (el.parentElement && parents.indexOf(el.parentElement) === -1) {
          parents.push(el.parentElement);
        }
      });
      parents.forEach(function (parent) {
        var kids = [];
        for (var i = 0; i < parent.children.length; i += 1) {
          if (parent.children[i].classList.contains("reveal")) {
            kids.push(parent.children[i]);
          }
        }
        kids.forEach(function (kid, index) {
          kid.style.setProperty("--reveal-delay", (index * 90) + "ms");
        });
      });
      if ("IntersectionObserver" in window) {
        var revealObserver = new IntersectionObserver(function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              entry.target.classList.add("is-in");
              revealObserver.unobserve(entry.target);
            }
          });
        }, { threshold: 0.16, rootMargin: "0px 0px -8% 0px" });
        revealNodes.forEach(function (el) {
          revealObserver.observe(el);
        });
      } else {
        revealNodes.forEach(function (el) {
          el.classList.add("is-in");
        });
      }
    }
  }

  document.querySelectorAll("[data-accordion]").forEach(function (accordion) {
    accordion.addEventListener("click", function (event) {
      var button = event.target.closest(".acc-btn");
      if (!button || !accordion.contains(button)) {
        return;
      }
      var item = button.parentElement;
      var willOpen = !item.classList.contains("is-open");
      accordion.querySelectorAll(".acc-item").forEach(function (node) {
        node.classList.remove("is-open");
        var btn = node.querySelector(".acc-btn");
        if (btn) {
          btn.setAttribute("aria-expanded", "false");
        }
      });
      if (willOpen) {
        item.classList.add("is-open");
        button.setAttribute("aria-expanded", "true");
      }
    });
  });

  function animateCount(el) {
    var end = parseInt(el.getAttribute("data-count"), 10);
    var suffix = el.getAttribute("data-suffix") || "";
    if (!end || el.dataset.done === "1") {
      return;
    }
    el.dataset.done = "1";
    var start = 0;
    var duration = end > 1000 ? 1800 : 1200;
    var started = null;

    function frame(ts) {
      if (!started) {
        started = ts;
      }
      var progress = Math.min((ts - started) / duration, 1);
      var eased = 1 - Math.pow(1 - progress, 3);
      el.textContent = Math.round(start + (end - start) * eased) + suffix;
      if (progress < 1) {
        window.requestAnimationFrame(frame);
      }
    }

    window.requestAnimationFrame(frame);
  }

  var counters = document.querySelectorAll("[data-count]");
  if (counters.length && "IntersectionObserver" in window) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          animateCount(entry.target);
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.4 });
    counters.forEach(function (el) {
      observer.observe(el);
    });
  } else {
    counters.forEach(animateCount);
  }

  document.querySelectorAll("[data-slider]").forEach(function (slider) {
    var track = slider.querySelector(".slider-track");
    var slides = slider.querySelectorAll(".slider-slide");
    var dotsWrap = slider.querySelector("[data-dots]");
    var prev = slider.querySelector("[data-prev]");
    var next = slider.querySelector("[data-next]");
    var index = 0;
    var timer = null;

    if (!track || !slides.length) {
      return;
    }

    function perView() {
      return window.matchMedia("(max-width: 980px)").matches ? 1 : 2;
    }

    function goTo(nextIndex) {
      var visible = perView();
      var max = Math.max(0, slides.length - visible);
      if (nextIndex > max) {
        index = 0;
      } else if (nextIndex < 0) {
        index = max;
      } else {
        index = nextIndex;
      }
      track.style.transform = "translateX(-" + (index * (100 / visible)) + "%)";
      if (dotsWrap) {
        dotsWrap.querySelectorAll("button").forEach(function (dot, i) {
          dot.classList.toggle("is-active", i === index);
        });
      }
    }

    function start() {
      stop();
      timer = window.setInterval(function () {
        goTo(index + 1);
      }, 6000);
    }

    function stop() {
      if (timer) {
        window.clearInterval(timer);
        timer = null;
      }
    }

    if (dotsWrap) {
      slides.forEach(function (_, i) {
        var dot = document.createElement("button");
        dot.type = "button";
        dot.setAttribute("aria-label", "Go to testimonial " + (i + 1));
        dot.addEventListener("click", function () {
          goTo(i);
          start();
        });
        dotsWrap.appendChild(dot);
      });
    }

    if (prev) {
      prev.addEventListener("click", function () {
        goTo(index - 1);
        start();
      });
    }
    if (next) {
      next.addEventListener("click", function () {
        goTo(index + 1);
        start();
      });
    }

    slider.addEventListener("mouseenter", stop);
    slider.addEventListener("mouseleave", start);
    window.addEventListener("resize", function () {
      goTo(index);
    });
    goTo(0);
    start();
  });

  function closeSelects(except) {
    document.querySelectorAll(".hw-select.is-open").forEach(function (wrap) {
      if (wrap !== except) {
        wrap.classList.remove("is-open");
        var list = wrap.querySelector(".hw-select-list");
        if (list) {
          list.hidden = true;
        }
      }
    });
  }

  document.querySelectorAll(".lead-form select").forEach(function (select) {
    if (select.closest(".hw-select")) {
      return;
    }
    var wrap = document.createElement("div");
    wrap.className = "hw-select" + (select.value ? "" : " is-empty");
    select.parentNode.insertBefore(wrap, select);
    wrap.appendChild(select);
    select.classList.add("hw-select-native");
    select.tabIndex = -1;

    var button = document.createElement("button");
    button.type = "button";
    button.className = "hw-select-btn";
    button.setAttribute("aria-haspopup", "listbox");
    var label = document.createElement("span");
    var selected = select.options[select.selectedIndex];
    label.textContent = selected && selected.value ? selected.textContent : (select.options[0] ? select.options[0].textContent : "Select");
    button.appendChild(label);
    button.insertAdjacentHTML("beforeend", '<svg viewBox="0 0 20 20" width="16" height="16" aria-hidden="true"><path fill="currentColor" d="M5.2 7.4 10 12.2l4.8-4.8 1.4 1.4L10 15 3.8 8.8z"/></svg>');
    wrap.appendChild(button);

    var list = document.createElement("div");
    list.className = "hw-select-list";
    list.hidden = true;
    list.setAttribute("role", "listbox");
    Array.prototype.forEach.call(select.options, function (option) {
      if (!option.value) {
        return;
      }
      var item = document.createElement("button");
      item.type = "button";
      item.textContent = option.textContent;
      item.setAttribute("data-value", option.value);
      if (option.selected && option.value) {
        item.classList.add("is-active");
      }
      item.addEventListener("click", function () {
        select.value = option.value;
        select.dispatchEvent(new Event("change", { bubbles: true }));
        label.textContent = option.textContent;
        wrap.classList.remove("is-empty");
        list.querySelectorAll("button").forEach(function (btn) {
          btn.classList.toggle("is-active", btn === item);
        });
        closeSelects();
      });
      list.appendChild(item);
    });
    wrap.appendChild(list);

    button.addEventListener("click", function () {
      var open = wrap.classList.toggle("is-open");
      if (open) {
        closeSelects(wrap);
      }
      list.hidden = !open;
    });
  });

  document.addEventListener("click", function (event) {
    if (!event.target.closest(".hw-select")) {
      closeSelects();
    }
  });
})();
