(function () {
  function updateSummary(summary) {
    if (!summary) {
      return;
    }

    var score = document.querySelector('[data-arwp-summary="score"]');
    var level = document.querySelector('[data-arwp-summary="level_name"]');
    var scannedAt = document.querySelector('[data-arwp-summary="scanned_at"]');
    var message = document.querySelector('[data-arwp-summary="message"]');
    var groups = document.querySelector('[data-arwp-summary="groups"]');

    if (score) {
      score.textContent = summary.score || 0;
    }
    if (level) {
      level.textContent = summary.level_name || '';
    }
    if (scannedAt) {
      scannedAt.textContent = summary.scanned_at || '';
    }
    if (message) {
      message.textContent = summary.message || '';
    }
    if (groups && Array.isArray(summary.groups)) {
      groups.innerHTML = '';
      summary.groups.forEach(function (group) {
        var item = document.createElement('li');
        item.className = 'arwp-state-' + (group.state || 'fail');
        item.textContent = group.total === 0
          ? group.label + ': ' + (window.arwpSettingsPage.messages.notChecked || 'Not checked')
          : group.label + ': ' + group.passed + '/' + group.total;
        groups.appendChild(item);
      });
    }
  }

  function valueOrMissing(value, labels) {
    if (!value) {
      return labels.missingLabel;
    }

    return String(value);
  }

  function splitUrlList(value) {
    return String(value || '')
      .split(/\n|,/)
      .map(function (entry) {
        return entry.trim();
      })
      .filter(function (entry) {
        return entry !== '';
      });
  }

  function setPhaseTwoPreview(sectionKey, text) {
    var target = document.querySelector('[data-arwp-phase2-preview="' + sectionKey + '"]');
    if (target) {
      target.textContent = text;
    }
  }

  function toggleApplicabilityState() {
    var protectedToggle = document.getElementById('arwp-protected-apis-enabled');
    if (!protectedToggle) {
      return;
    }

    var enabled = !!protectedToggle.checked;
    var sections = document.querySelectorAll('[data-arwp-phase2-conditional]');
    sections.forEach(function (section) {
      section.classList.toggle('arwp-phase2-section-disabled', !enabled);
      section.setAttribute('aria-disabled', enabled ? 'false' : 'true');

      var controls = section.querySelectorAll('input, textarea, select');
      controls.forEach(function (control) {
        control.disabled = !enabled;
      });

      var reason = section.querySelector('.arwp-phase2-disabled-reason');
      if (reason) {
        reason.style.display = enabled ? 'none' : '';
      }
    });
  }

  function updatePhaseTwoPreview() {
    if (!window.arwpSettingsPage || !window.arwpSettingsPage.phase2) {
      return;
    }

    var labels = window.arwpSettingsPage.phase2;

    var mcpEnabled = document.querySelector('input[name="agent_ready_wp_settings[mcp_server_card][enabled]"]:checked');
    var mcpName = document.querySelector('input[name="agent_ready_wp_settings[mcp_server_card][name]"]');
    var mcpVersion = document.querySelector('input[name="agent_ready_wp_settings[mcp_server_card][version]"]');
    var mcpTransport = document.querySelector('input[name="agent_ready_wp_settings[mcp_server_card][transport]"]');

    if (mcpEnabled && mcpEnabled.value === '1') {
      setPhaseTwoPreview(
        'mcp_server_card',
        labels.serverLabel + ': ' + valueOrMissing(mcpName ? mcpName.value : '', labels) + ', ' +
          labels.versionLabel + ': ' + valueOrMissing(mcpVersion ? mcpVersion.value : '', labels) + ', ' +
          labels.transportLabel + ': ' + valueOrMissing(mcpTransport ? mcpTransport.value : '', labels)
      );
    } else {
      setPhaseTwoPreview('mcp_server_card', labels.disabledLabel);
    }

    var protectedToggle = document.getElementById('arwp-protected-apis-enabled');
    var protectedEnabled = !!(protectedToggle && protectedToggle.checked);

    var oauthEnabled = document.querySelector('input[name="agent_ready_wp_settings[oauth][enabled]"]:checked');
    var oauthIssuer = document.querySelector('input[name="agent_ready_wp_settings[oauth][issuer]"]');
    var oauthAuthorization = document.querySelector('input[name="agent_ready_wp_settings[oauth][authorization_endpoint]"]');
    var oauthToken = document.querySelector('input[name="agent_ready_wp_settings[oauth][token_endpoint]"]');
    var oauthJwks = document.querySelector('input[name="agent_ready_wp_settings[oauth][jwks_uri]"]');

    if (!protectedEnabled) {
      setPhaseTwoPreview('oauth_discovery', labels.notApplicableLabel);
    } else if (!(oauthEnabled && oauthEnabled.value === '1')) {
      setPhaseTwoPreview('oauth_discovery', labels.disabledLabel);
    } else {
      setPhaseTwoPreview(
        'oauth_discovery',
        labels.issuerLabel + ': ' + valueOrMissing(oauthIssuer ? oauthIssuer.value : '', labels) + ' | ' +
          labels.authorizationEndpointLabel + ': ' + valueOrMissing(oauthAuthorization ? oauthAuthorization.value : '', labels) + ' | ' +
          labels.tokenEndpointLabel + ': ' + valueOrMissing(oauthToken ? oauthToken.value : '', labels) + ' | ' +
          labels.jwksLabel + ': ' + valueOrMissing(oauthJwks ? oauthJwks.value : '', labels)
      );
    }

    var resourceEnabled = document.querySelector('input[name="agent_ready_wp_settings[protected_resource][enabled]"]:checked');
    var resourceUrl = document.querySelector('input[name="agent_ready_wp_settings[protected_resource][resource]"]');
    var resourceServers = document.querySelector('textarea[name="agent_ready_wp_settings[protected_resource][authorization_servers]"]');

    if (!protectedEnabled) {
      setPhaseTwoPreview('protected_resource', labels.notApplicableLabel);
    } else if (!(resourceEnabled && resourceEnabled.value === '1')) {
      setPhaseTwoPreview('protected_resource', labels.disabledLabel);
    } else {
      var servers = splitUrlList(resourceServers ? resourceServers.value : '');
      var renderedServers = servers.length ? servers.join(' | ') : labels.missingLabel;
      setPhaseTwoPreview(
        'protected_resource',
        labels.resourceLabel + ': ' + valueOrMissing(resourceUrl ? resourceUrl.value : '', labels) + ' | ' +
          labels.authorizationServerLabel + ': ' + renderedServers
      );
    }
  }

  function bindPhaseTwoInteractions() {
    var root = document.getElementById('arwp-phase-two-sections');
    if (!root) {
      return;
    }

    root.addEventListener('input', function () {
      toggleApplicabilityState();
      updatePhaseTwoPreview();
    });

    root.addEventListener('change', function () {
      toggleApplicabilityState();
      updatePhaseTwoPreview();
    });

    toggleApplicabilityState();
    updatePhaseTwoPreview();
  }

  document.addEventListener('DOMContentLoaded', function () {
    var button = document.getElementById('arwp-run-scan');
    var status = document.getElementById('arwp-scan-status');

    bindPhaseTwoInteractions();

    if (!button || !window.arwpSettingsPage) {
      return;
    }

    button.addEventListener('click', function () {
      var body = new window.URLSearchParams();
      body.append('action', 'arwp_run_scan');
      body.append('nonce', window.arwpSettingsPage.nonce);

      button.disabled = true;
      if (status) {
        status.style.display = 'block';
        status.className = 'notice notice-info inline';
        status.querySelector('p').textContent = window.arwpSettingsPage.messages.running;
      }

      window
        .fetch(window.arwpSettingsPage.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
          },
          body: body.toString()
        })
        .then(function (response) {
          return response.json();
        })
        .then(function (payload) {
          var data = payload.data || {};
          updateSummary(data.summary || null);

          if (status) {
            status.className = payload.success ? 'notice notice-success inline' : 'notice notice-error inline';
            status.querySelector('p').textContent = data.message || window.arwpSettingsPage.messages.failed;
          }
        })
        .catch(function () {
          if (status) {
            status.className = 'notice notice-error inline';
            status.querySelector('p').textContent = window.arwpSettingsPage.messages.failed;
          }
        })
        .finally(function () {
          button.disabled = false;
        });
    });
  });
})();
