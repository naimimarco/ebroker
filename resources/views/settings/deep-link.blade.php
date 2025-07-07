<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
$(document).ready(function () {
    let appScheme = '{{$appName}}://' + window.location.host + window.location.pathname;
    let androidAppStoreLink = '{{$customerPlayStoreUrl}}';
    let iosAppStoreLink = '{{$customerAppStoreUrl}}';
    let userAgent = navigator.userAgent || navigator.vendor || window.opera;
    let isAndroid = /android/i.test(userAgent);
    let isIOS = /iPad|iPhone|iPod/.test(userAgent) && !window.MSStream;
    let appStoreLink = isAndroid ? androidAppStoreLink : (isIOS ? iosAppStoreLink : androidAppStoreLink);
    window.location.href = appScheme;
    setTimeout(function () {
        if (!document.hidden && !document.webkitHidden) {
            if (confirm("{{$appName}} app is not installed. Would you like to download it from the app store?")) {
                window.location.href = appStoreLink;
            }
        }
    }, 1000);
});
</script>
