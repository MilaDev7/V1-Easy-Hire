var currentProIdForRequest = null;

function showDirectRequestModal() {
    if (!currentProIdForRequest) {
        alert('Please select a professional first');
        return;
    }
    
    var token = localStorage.getItem('token');
    if (!token) {
        alert('Please login to send a request');
        return;
    }
    
    document.getElementById('direct-request-pro-id').value = currentProIdForRequest;
    document.getElementById('direct-request-title').value = '';
    document.getElementById('direct-request-desc').value = '';
    document.getElementById('direct-request-budget').value = '';
    document.getElementById('direct-request-feedback').innerHTML = '';
    
    var proProfileModal = document.getElementById('pro-profile-modal');
    var directRequestModal = document.getElementById('direct-request-modal');
    
    if (proProfileModal) {
        bootstrap.Modal.getOrCreateInstance(proProfileModal).hide();
    }
    if (directRequestModal) {
        bootstrap.Modal.getOrCreateInstance(directRequestModal).show();
    }
}

function sendDirectRequest() {
    var proId = document.getElementById('direct-request-pro-id').value;
    var title = document.getElementById('direct-request-title').value.trim();
    var description = document.getElementById('direct-request-desc').value.trim();
    var budget = document.getElementById('direct-request-budget').value;
    var feedback = document.getElementById('direct-request-feedback');
    var btn = document.getElementById('send-direct-request-btn');
    
    if (!title || !description) {
        feedback.innerHTML = '<div class="alert alert-danger mb-0">Please fill title and description</div>';
        return;
    }
    
    var token = localStorage.getItem('token');
    if (!token) {
        alert('Please login to send a request');
        return;
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Sending...';
    }

    fetch('/api/send-request/' + proId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
        },
        body: JSON.stringify({
            title: title,
            description: description,
            budget: budget || null
        })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            feedback.innerHTML = '<div class="alert alert-success mb-0"><i class="fa-solid fa-check-circle me-1"></i> Request sent successfully!</div>';
            setTimeout(function() {
                bootstrap.Modal.getInstance(document.getElementById('direct-request-modal')).hide();
            }, 1500);
        } else {
            feedback.innerHTML = '<div class="alert alert-danger mb-0">' + (data.message || 'Failed to send request') + '</div>';
        }
    })
    .catch(function(err) {
        feedback.innerHTML = '<div class="alert alert-danger mb-0">Error: ' + (err.message || 'Failed to send request') + '</div>';
    })
    .finally(function() {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Send Request';
        }
    });
}

// Expose functions globally
window.showDirectRequestModal = showDirectRequestModal;
window.sendDirectRequest = sendDirectRequest;
