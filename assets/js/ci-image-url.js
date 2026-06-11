(function (window) {
  var legacyDomains = [
    'https://cashforcars.local',
    'http://cashforcars.local'
  ];

  function ciNormalizeImageUrl(url) {
    if (!url) {
      return url;
    }

    var normalized = url;

    if (normalized.indexOf('/wp-content/') === 0) {
      return window.location.origin + normalized;
    }

    legacyDomains.forEach(function (legacyDomain) {
      if (normalized.indexOf(legacyDomain) === 0) {
        normalized = window.location.origin + normalized.substring(legacyDomain.length);
      }
    });

    return normalized;
  }

  function ciApplyImageUrlNormalization(root) {
    var scope = root || document;
    var images = scope.querySelectorAll('img[data-ci-image], .preview-image');

    images.forEach(function (img) {
      var source = img.getAttribute('data-ci-image') || img.getAttribute('src');
      if (!source) {
        return;
      }

      var normalized = ciNormalizeImageUrl(source);
      img.setAttribute('src', normalized);
      img.setAttribute('data-ci-image', normalized);
    });
  }

  window.ciNormalizeImageUrl = ciNormalizeImageUrl;
  window.ciApplyImageUrlNormalization = ciApplyImageUrlNormalization;

  document.addEventListener('DOMContentLoaded', function () {
    ciApplyImageUrlNormalization();
  });
})(window);
