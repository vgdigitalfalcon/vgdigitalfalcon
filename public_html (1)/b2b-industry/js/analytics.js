// assets/js/analytics.js
// Google Analytics & Tag Manager Integration

(function() {
    // Initialize Google Analytics
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    window.gtag = gtag;
    
    // Load GA script
    var script = document.createElement('script');
    script.async = true;
    script.src = 'https://www.googletagmanager.com/gtag/js?id=G-SHM6SNGHL8';
    document.head.appendChild(script);
    
    // Initialize
    gtag('js', new Date());
    gtag('config', 'G-SHM6SNGHL8');
    
    console.log('✅ Google Analytics loaded');
})();

// If you want Google Tag Manager instead:
/*
(function(w,d,s,l,i){
    w[l]=w[l]||[];
    w[l].push({'gtm.start': new Date().getTime(), event:'gtm.js'});
    var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),
        dl=l!='dataLayer'?'&l='+l:'';
    j.async=true;
    j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;
    f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-XXXXXXX');
*/