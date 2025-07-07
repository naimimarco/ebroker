<script>
    const urlParams = new URLSearchParams(window.location.search);
    const trxref = urlParams.get('trxref');
    const reference = urlParams.get('reference');

    // Check if this is the original window (not a child window)
    if (!window.opener) {
        const newWindow = window.open(window.location.href, '_blank', 'width=600,height=400');
        if (newWindow) {
            window.close(); // Close the current window after opening the new one
        }
    } else {
        // This runs only in the newly opened window
        window.opener.postMessage({
            status: 'success',
            reference: reference || 'your-payment-reference',
            trxref: trxref
        }, '*');

        window.close(); // Close the child window after sending the message
    }
</script>
