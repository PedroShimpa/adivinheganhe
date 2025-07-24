{{-- components/banner.blade.php --}}
<div id="terra-banner-container-{{ $adId ?? 'default' }}"></div>

@once
<script>
function loadTerraBanner(containerId, key) {
    const container = document.getElementById(containerId);
    if (!container) return;

    container.innerHTML = '';

    const configScript = document.createElement('script');
    configScript.type = 'text/javascript';
    configScript.text = `
        atOptions = {
            'key': '${key}',
            'format': 'iframe',
            'height': 90,
            'width': 728,
            'params': {}
        };
    `;

    const invokeScript = document.createElement('script');
    invokeScript.type = 'text/javascript';
    invokeScript.async = true;
    invokeScript.src = "//www.highperformanceformat.com/" + key + "/invoke.js";

    container.appendChild(configScript);
    container.appendChild(invokeScript);
}
</script>
@endonce

<script>
    loadTerraBanner('terra-banner-container-{{ $adId ?? 'default' }}', "{{ env('ADS_TERRA_BANNER_CODE') }}");
</script>
