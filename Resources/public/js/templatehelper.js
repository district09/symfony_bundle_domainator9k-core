(function (document, window) {
  window.templateHelpers = {};
  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('dialog[data-template-helper-textarea]').forEach(function (dialog) {
      if (!dialog.dataset.templateHelperProcessed) {
        var selector = dialog.dataset.templateHelperTextarea;
        window.templateHelpers[selector] = new TemplateHelper(dialog);
        dialog.dataset.templateHelperProcessed = true;
      }
    });
  });

  function TemplateHelper(dialog) {
    var self = this;
    self.dialog = dialog;
    self.textareas = new Array();
    self.openDialogLinks = {}
    document.querySelectorAll(dialog.dataset.templateHelperTextarea).forEach(function (textarea) {
      self.registerTextarea(textarea);
    });
    self.bindDialogClose();
    self.bindTemplateLinks();
  }

  TemplateHelper.prototype.registerTextarea = function (textarea) {
    var self = this;
    self.textareas.push(textarea);
    self.activeTextarea = textarea;
    self.createDialogLink(textarea);
  };

  TemplateHelper.prototype.bindDialogClose = function () {
    var self = this;
    self.dialog.querySelectorAll('.close-template-dialog')[0].addEventListener('click', function (e) {
      e.preventDefault();
      self.closeDialog();
    });
  };

  TemplateHelper.prototype.openDialog = function () {
    var self = this;
    self.dialog.showModal();
  };

  TemplateHelper.prototype.closeDialog = function () {
    var self = this;
    self.dialog.close();
  };

  TemplateHelper.prototype.createDialogLink = function (textarea) {
    var self = this;
    var link = document.createElement('a');
    link.href = '#';
    link.classList.add('template-helper-dialog-link');
    link.appendChild(document.createTextNode('Insert template value'));
    link.addEventListener('click', function (e) {
      e.preventDefault();
      self.activeTextarea = textarea;
      self.openDialog();
    });
    textarea.parentNode.insertBefore(link, textarea.nextSibling);
    self.openDialogLinks[textarea.name] = link;
  };

  TemplateHelper.prototype.bindTemplateLinks = function() {
    var self = this;
    self.dialog.querySelectorAll('a[data-template-value]').forEach(function (link) {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        self.insertTemplate(link.dataset.templateValue);
      });
    });
  };

  TemplateHelper.prototype.insertTemplate = function (template) {
    var self = this;
    self.activeTextarea.value = self.activeTextarea.value + template;
    var event = new Event('change');
    self.activeTextarea.dispatchEvent(event);
  };
})(document, window);
