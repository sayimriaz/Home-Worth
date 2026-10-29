(function () {
  var toast = document.getElementById("confirm-toast");
  var title = document.getElementById("confirm-toast-title");
  var confirmBtn = toast ? toast.querySelector("[data-confirm]") : null;
  var onConfirm = null;

  function closeToast() {
    onConfirm = null;
    if (toast) {
      toast.hidden = true;
    }
  }

  function ask(message, actionLabel, danger, next) {
    if (!toast || !title || !confirmBtn) {
      next();
      return;
    }
    title.textContent = message;
    confirmBtn.textContent = actionLabel;
    confirmBtn.classList.toggle("is-gold", !danger);
    onConfirm = next;
    toast.hidden = false;
  }

  if (toast) {
    var cancel = toast.querySelector("[data-cancel]");
    if (cancel) {
      cancel.addEventListener("click", closeToast);
    }
    if (confirmBtn) {
      confirmBtn.addEventListener("click", function () {
        var next = onConfirm;
        closeToast();
        if (next) {
          next();
        }
      });
    }
    toast.addEventListener("click", function (event) {
      if (event.target === toast) {
        closeToast();
      }
    });
  }

  var bulkForm = document.getElementById("bulk-delete");
  var bulkBtn = document.getElementById("bulk-delete-btn");
  var selectAll = document.getElementById("select-all");
  var rowChecks = Array.prototype.slice.call(document.querySelectorAll(".row-check"));

  function selectedCount() {
    return rowChecks.filter(function (box) {
      return box.checked;
    }).length;
  }

  function syncBulk() {
    var count = selectedCount();
    if (bulkBtn) {
      bulkBtn.disabled = count === 0;
    }
    if (selectAll) {
      selectAll.checked = rowChecks.length > 0 && count === rowChecks.length;
    }
  }

  rowChecks.forEach(function (box) {
    box.addEventListener("change", syncBulk);
  });
  if (selectAll) {
    selectAll.addEventListener("change", function () {
      rowChecks.forEach(function (box) {
        box.checked = selectAll.checked;
      });
      syncBulk();
    });
  }
  if (bulkForm) {
    bulkForm.addEventListener("submit", function (event) {
      event.preventDefault();
      var count = selectedCount();
      if (count === 0) {
        return;
      }
      var label = count === 1 ? "Delete this entry?" : "Delete " + count + " selected entries?";
      ask(label, "Delete", true, function () {
        bulkForm.submit();
      });
    });
  }
  syncBulk();

  document.querySelectorAll(".admin-delete").forEach(function (form) {
    form.addEventListener("submit", function (event) {
      event.preventDefault();
      ask("Delete this entry?", "Delete", true, function () {
        form.submit();
      });
    });
  });

  function selectedIds() {
    return rowChecks.filter(function (box) {
      return box.checked;
    }).map(function (box) {
      return box.value;
    });
  }

  var formModal = document.getElementById("form-modal");
  var formFrame = document.getElementById("form-modal-frame");
  var formPrint = document.getElementById("form-modal-print");
  var formClose = document.getElementById("form-modal-close");

  function formEmbedUrl(url) {
    if (!url) {
      return "";
    }
    if (/[?&]embed=/.test(url)) {
      return url;
    }
    return url + (url.indexOf("?") >= 0 ? "&" : "?") + "embed=1";
  }

  function closeFormModal() {
    if (!formModal) {
      return;
    }
    formModal.hidden = true;
    document.body.classList.remove("hw-form-open");
    if (formFrame) {
      formFrame.removeAttribute("src");
    }
  }

  function openFormModal(url) {
    if (!url) {
      return;
    }
    if (!formModal || !formFrame) {
      window.location.href = url;
      return;
    }
    formModal.hidden = false;
    document.body.classList.add("hw-form-open");
    formFrame.src = formEmbedUrl(url);
    if (formClose) {
      formClose.focus();
    }
  }

  if (formClose) {
    formClose.addEventListener("click", closeFormModal);
  }
  if (formPrint) {
    formPrint.addEventListener("click", function () {
      if (formFrame && formFrame.contentWindow) {
        formFrame.contentWindow.focus();
        formFrame.contentWindow.print();
      }
    });
  }
  if (formModal) {
    formModal.addEventListener("click", function (event) {
      if (event.target === formModal) {
        closeFormModal();
      }
    });
  }
  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape" && formModal && !formModal.hidden) {
      closeFormModal();
    }
  });
  window.addEventListener("message", function (event) {
    if (event.origin !== window.location.origin || event.data !== "hw-form-close") {
      return;
    }
    closeFormModal();
  });
  document.querySelectorAll(".admin-form-view").forEach(function (link) {
    link.addEventListener("click", function (event) {
      if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) {
        return;
      }
      event.preventDefault();
      openFormModal(link.getAttribute("href"));
    });
  });

  function openForms(download) {
    var button = download ? document.getElementById("download-form-btn") : document.getElementById("view-form-btn");
    if (!button) {
      return;
    }
    var ids = selectedIds();
    if (ids.length === 0) {
      ask("Select at least one entry first.", "OK", false, function () {});
      return;
    }
    var url = button.getAttribute("data-url") + "?type=" + encodeURIComponent(button.getAttribute("data-type") || "leads") + "&ids=" + encodeURIComponent(ids.join(","));
    if (download) {
      url += "&download=1";
      window.location.href = url;
      return;
    }
    openFormModal(url);
  }

  var viewFormBtn = document.getElementById("view-form-btn");
  if (viewFormBtn) {
    viewFormBtn.addEventListener("click", function () {
      openForms(false);
    });
  }
  var downloadFormBtn = document.getElementById("download-form-btn");
  if (downloadFormBtn) {
    downloadFormBtn.addEventListener("click", function () {
      openForms(true);
    });
  }

  var exportBtn = document.getElementById("export-btn");
  if (exportBtn) {
    exportBtn.addEventListener("click", function () {
      var href = exportBtn.getAttribute("data-href");
      if (!href) {
        return;
      }
      ask("Export these entries?", "Export", false, function () {
        window.location.href = href;
      });
    });
  }

  var signout = document.getElementById("signout-link");
  if (signout) {
    signout.addEventListener("click", function (event) {
      event.preventDefault();
      var href = signout.getAttribute("href");
      if (!href) {
        return;
      }
      ask("Sign out of admin?", "Sign out", false, function () {
        window.location.href = href;
      });
    });
  }

  var flash = document.querySelector(".admin-flash");
  if (flash) {
    window.setTimeout(function () {
      flash.remove();
    }, 3200);
  }

  var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
  var week = ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"];
  var cal = null;
  var calView = new Date();
  var calTarget = null;

  function pad(n) {
    return n < 10 ? "0" + n : String(n);
  }
  function toIso(date) {
    return date.getFullYear() + "-" + pad(date.getMonth() + 1) + "-" + pad(date.getDate());
  }
  function pretty(iso) {
    if (!iso) {
      return "Select date";
    }
    var parts = iso.split("-");
    var month = months[parseInt(parts[1], 10) - 1];
    return parseInt(parts[2], 10) + " " + (month ? month.slice(0, 3) : "") + " " + parts[0];
  }
  function parseIso(iso) {
    if (!iso) {
      return null;
    }
    var parts = iso.split("-");
    var date = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
    return isNaN(date.getTime()) ? null : date;
  }
  function setDateValue(wrap, iso) {
    var input = wrap.querySelector("input");
    var button = wrap.querySelector(".hw-date-btn");
    if (input) {
      input.value = iso || "";
    }
    if (button) {
      button.textContent = pretty(iso);
      button.classList.toggle("is-empty", !iso);
    }
  }
  function closeCal() {
    if (cal) {
      cal.hidden = true;
    }
    document.querySelectorAll(".hw-date.is-open").forEach(function (el) {
      el.classList.remove("is-open");
    });
    calTarget = null;
  }
  function drawCal() {
    if (!cal || !calTarget) {
      return;
    }
    var label = cal.querySelector("[data-label]");
    var grid = cal.querySelector("[data-days]");
    var picked = calTarget.querySelector("input").value;
    var year = calView.getFullYear();
    var month = calView.getMonth();
    label.textContent = months[month] + " " + year;
    var first = new Date(year, month, 1);
    var start = first.getDay();
    var daysInMonth = new Date(year, month + 1, 0).getDate();
    var prevDays = new Date(year, month, 0).getDate();
    var today = toIso(new Date());
    var html = "";
    var i;
    for (i = 0; i < 42; i++) {
      var dayNum;
      var date;
      var muted = false;
      if (i < start) {
        dayNum = prevDays - start + i + 1;
        date = new Date(year, month - 1, dayNum);
        muted = true;
      } else if (i - start + 1 > daysInMonth) {
        dayNum = i - start - daysInMonth + 1;
        date = new Date(year, month + 1, dayNum);
        muted = true;
      } else {
        dayNum = i - start + 1;
        date = new Date(year, month, dayNum);
      }
      var value = toIso(date);
      var cls = "hw-cal-day";
      if (muted) {
        cls += " is-muted";
      }
      if (value === today) {
        cls += " is-today";
      }
      if (value === picked) {
        cls += " is-picked";
      }
      html += '<button type="button" class="' + cls + '" data-iso="' + value + '">' + dayNum + "</button>";
    }
    grid.innerHTML = html;
  }
  function openCal(wrap) {
    if (!cal) {
      return;
    }
    calTarget = wrap;
    var current = parseIso(wrap.querySelector("input").value) || new Date();
    calView = new Date(current.getFullYear(), current.getMonth(), 1);
    wrap.classList.add("is-open");
    wrap.appendChild(cal);
    cal.hidden = false;
    drawCal();
  }

  document.querySelectorAll("[data-date]").forEach(function (wrap) {
    setDateValue(wrap, wrap.querySelector("input").value);
  });

  if (document.querySelector("[data-date]")) {
    cal = document.createElement("div");
    cal.className = "hw-cal";
    cal.hidden = true;
    cal.innerHTML =
      '<div class="hw-cal-head"><button type="button" data-prev aria-label="Previous month">‹</button><strong data-label></strong><button type="button" data-next aria-label="Next month">›</button></div>' +
      '<div class="hw-cal-week">' + week.map(function (day) { return "<span>" + day + "</span>"; }).join("") + "</div>" +
      '<div class="hw-cal-days" data-days></div>' +
      '<div class="hw-cal-foot"><button type="button" data-today>Today</button><button type="button" data-wipe>Clear</button></div>';
    document.body.appendChild(cal);
    cal.addEventListener("click", function (event) {
      event.stopPropagation();
      var prev = event.target.closest("[data-prev]");
      var next = event.target.closest("[data-next]");
      var day = event.target.closest("[data-iso]");
      var todayBtn = event.target.closest("[data-today]");
      var wipe = event.target.closest("[data-wipe]");
      if (prev) {
        calView.setMonth(calView.getMonth() - 1);
        drawCal();
      }
      if (next) {
        calView.setMonth(calView.getMonth() + 1);
        drawCal();
      }
      if (day && calTarget) {
        setDateValue(calTarget, day.getAttribute("data-iso"));
        closeCal();
      }
      if (todayBtn && calTarget) {
        setDateValue(calTarget, toIso(new Date()));
        closeCal();
      }
      if (wipe && calTarget) {
        setDateValue(calTarget, "");
        closeCal();
      }
    });
    document.querySelectorAll("[data-date]").forEach(function (wrap) {
      wrap.querySelector(".hw-date-btn").addEventListener("click", function (event) {
        event.preventDefault();
        event.stopPropagation();
        if (wrap.classList.contains("is-open")) {
          closeCal();
          return;
        }
        closeCal();
        openCal(wrap);
      });
    });
    document.addEventListener("click", closeCal);
  }
})();
