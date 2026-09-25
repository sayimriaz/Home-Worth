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

  document.querySelectorAll(".admin-delete").forEach(function (form) {
    form.addEventListener("submit", function (event) {
      event.preventDefault();
      ask("Delete this entry?", "Delete", true, function () {
        form.submit();
      });
    });
  });

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

  var drawer = document.getElementById("msg-drawer");
  if (drawer) {
    var meta = drawer.querySelector("[data-meta]");
    var body = drawer.querySelector("[data-body]");
    var close = function () {
      drawer.hidden = true;
    };
    document.querySelectorAll(".admin-view").forEach(function (button) {
      button.addEventListener("click", function () {
        if (meta) {
          meta.textContent = [
            button.getAttribute("data-name"),
            button.getAttribute("data-email"),
            button.getAttribute("data-date")
          ].filter(Boolean).join(" · ");
        }
        if (body) {
          body.textContent = button.getAttribute("data-message") || "";
        }
        drawer.hidden = false;
      });
    });
    drawer.addEventListener("click", function (event) {
      if (event.target === drawer) {
        close();
      }
    });
    var closeBtn = drawer.querySelector("[data-close]");
    if (closeBtn) {
      closeBtn.addEventListener("click", close);
    }
  }

  var flash = document.querySelector(".admin-flash");
  if (flash) {
    window.setTimeout(function () {
      flash.remove();
    }, 3200);
  }
})();
