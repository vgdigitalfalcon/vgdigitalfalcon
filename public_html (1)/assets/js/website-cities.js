// assets/js/website-cities.js
// Dynamic city links based on service slug from data attribute
// 287 cities across Tamil Nadu, Kerala, Karnataka & Andhra Pradesh

document.addEventListener("DOMContentLoaded", () => {

    const cityGroups = [
        {
            state: "Tamil Nadu",
            cities: [
                "Madurai", "Chennai", "Trichy", "Coimbatore", "Erode",
                "Namakkal", "Tirunelveli", "Nagercoil", "Kanniyakumari", "Salem",
                "Tiruppur", "Vellore", "Thoothukudi", "Cuddalore", "Dharmapuri",
                "Dindigul", "Karur", "Krishnagiri", "Viluppuram", "Sivakasi",
                "Thanjavur", "Theni", "Nagapattinam", "Tiruvannamalai", "Kanchipuram",
                "Tiruvallur", "Ranipet", "Sivagangai", "Virudhunagar", "Ooty",
                "Perundurai", "Coonoor", "Kumbakonam", "Ramanathapuram", "Kovilpatti",
                "Sattur", "Rajapalayam", "Pondicherry", "Marthandam", "Hosur",
                "Tiruchengode", "Pollachi", "Gobichettipalayam", "Mettupalayam",
                "Udumalpet", "Avinashi", "Palladam", "Kangeyam", "Dharapuram",
                "Bhavani", "Sathyamangalam", "Anthiyur", "Komarapalayam", "Rasipuram",
                "Attur", "Mettur", "Edappadi", "Sankari", "Yercaud", "Arakkonam",
                "Gudiyatham", "Arcot", "Ambur", "Vaniyambadi", "Walajapet", "Arani",
                "Cheyyar", "Tindivanam", "Gingee", "Chidambaram", "Neyveli", "Panruti",
                "Vriddhachalam", "Mayiladuthurai", "Sirkazhi", "Mannargudi", "Pattukkottai",
                "Papanasam", "Orathanadu", "Perambalur", "Ariyalur", "Jayankondam",
                "Pudukkottai", "Aranthangi", "Karaikudi", "Devakottai", "Paramakudi",
                "Rameswaram", "Mudukulathur", "Usilampatti", "Melur", "Bodinayakanur",
                "Cumbum", "Palani", "Oddanchatram", "Nilakottai", "Aruppukkottai",
                "Tenkasi", "Sankarankovil", "Ambasamudram", "Chengalpattu", "Tambaram",
                "Avadi", "Poonamallee", "Red Hills", "Sholinganallur", "Kelambakkam",
                "Siruseri", "Mahabalipuram", "Uthiramerur", "Kalpakkam", "Sriperumbudur",
                "Manali", "Minjur", "Gummidipoondi", "Ponneri", "Periyakulam",
                "Batlagundu", "Andipatti", "Vadipatti", "Shencottai", "Alangulam",
                "Veerakeralamputhur", "Kayalpattinam", "Tiruchendur", "Ettayapuram",
                "Vilathikulam", "Srivilliputhur", "Watrap", "Ilayangudi", "Keelakarai",
                "Mandapam", "Thiruthuraipoondi", "Vedaranyam", "Velankanni", "Poompuhar",
                "Muthupet", "Lalgudi", "Manapparai", "Musiri", "Thuraiyur", "Kulithalai",
                "Harur", "Pennagaram", "Denkanikottai", "Bargur", "Uthangarai",
                "Valparai", "Kotagiri", "Gudalur"
            ]
        },
        {
            state: "Kerala",
            cities: [
                "Kochi", "Thiruvananthapuram", "Kozhikode", "Thrissur", "Kollam", "Kannur",
                "Alappuzha", "Kottayam", "Palakkad", "Malappuram", "Kasaragod", "Pathanamthitta",
                "Idukki", "Wayanad", "Ernakulam", "Kothamangalam", "Muvattupuzha", "Angamaly",
                "Aluva", "Perumbavoor", "Kalamassery", "Kakkanad", "Tripunithura", "North Paravur",
                "Piravom", "Changanassery", "Pala", "Vaikom", "Ettumanoor", "Thodupuzha",
                "Kattappana", "Munnar", "Nedumkandam", "Manjeri", "Tirur", "Perinthalmanna",
                "Nilambur", "Ponnani", "Kottakkal", "Kondotty", "Koyilandy", "Vadakara",
                "Ramanattukara", "Feroke", "Payyannur", "Taliparamba", "Mattannur", "Thalassery",
                "Kasaragod Town", "Kanhangad", "Hosdurg", "Cherthala", "Kayamkulam", "Haripad",
                "Adoor", "Pandalam", "Neyyattinkara", "Attingal", "Varkala", "Guruvayur"
            ]
        },
        {
            state: "Karnataka",
            cities: [
                "Bengaluru", "Mysuru", "Mangaluru", "Hubballi", "Dharwad", "Belagavi", "Ballari",
                "Shivamogga", "Davanagere", "Tumakuru", "Kalaburagi", "Vijayapura", "Raichur",
                "Bidar", "Kolar", "Chikkaballapur", "Ramanagara", "Mandya", "Hassan",
                "Chikkamagaluru", "Udupi", "Karwar", "Sirsi", "Bagalkot", "Gadag", "Haveri",
                "Chitradurga", "Koppal", "Yadgir", "Hospet", "Harihar", "Ranebennur", "Bhadravati",
                "Sagar", "Madikeri", "Virajpet", "Puttur", "Bantwal", "Moodbidri", "Kundapura",
                "Gokak", "Athani", "Jamkhandi", "Sedam", "Shahapur", "Sindhanur", "Mudhol",
                "Chintamani", "Nanjangud", "Kollegala", "Tiptur", "Arsikere", "Sakleshpur",
                "Doddaballapur", "Nelamangala", "Devanahalli", "Anekal", "Bidadi", "Malur", "Hoskote"
            ]
        },
        {
            state: "Andhra Pradesh",
            cities: [
                "Visakhapatnam", "Vijayawada", "Guntur", "Tirupati", "Kakinada", "Rajahmundry",
                "Nellore", "Kurnool", "Anantapur", "Kadapa", "Eluru", "Ongole", "Chittoor",
                "Machilipatnam", "Srikakulam", "Vizianagaram", "Hindupur", "Proddatur", "Tenali",
                "Nandyal", "Adoni", "Amalapuram", "Bhimavaram", "Tadepalligudem", "Narasaraopet",
                "Gudivada", "Chirala", "Madanapalle", "Dharmavaram", "Guntakal", "Tadipatri",
                "Kavali", "Markapur", "Palakollu", "Tanuku", "Bapatla", "Mangalagiri", "Sattenapalle",
                "Repalle", "Nuzvid", "Piduguralla", "Vinukonda", "Rayachoti", "Pulivendula",
                "Yemmiganur", "Allagadda", "Atmakur", "Naidupeta", "Sullurpeta", "Tuni", "Samalkot",
                "Peddapuram", "Ramachandrapuram", "Parvathipuram", "Bobbili", "Palasa", "Ichchapuram",
                "Narasapuram", "Avanigadda"
            ]
        }
    ];

    const container = document.getElementById("cities-container");

    if (container) {
        // Get service slug from data attribute
        const serviceSlug = container.getAttribute("data-service-slug") || "website-development-agency";

        let outputHTML = "";

        cityGroups.forEach(group => {
            // State heading
            outputHTML += `<div style="width:100%; text-align:left; margin: 22px 0 10px; padding-left: 4px;">
                <span style="font-size:13px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:#6e8efb; border-left:3px solid #6e8efb; padding-left:10px;">${group.state}</span>
            </div>`;

            // City pills
            group.cities.forEach(city => {
                const citySlug = city
                    .toLowerCase()
                    .replace(/\s+/g, '-')
                    .replace(/[^a-z0-9-]/g, '');

                // /service/{serviceSlug}-in-{citySlug}
                const targetUrl = `/service/${serviceSlug}-in-${citySlug}`;

                outputHTML += `<a 
                    href="${targetUrl}"
                    title="${city} ${serviceSlug.replace(/-/g, ' ')} services"
                    style="display:inline-block; padding:8px 20px; margin:5px; background:#fff; border:1px solid #e5e5e5; border-radius:50px; color:#444; text-decoration:none; font-size:14px; font-weight:500; transition:all 0.3s ease; box-shadow:0 2px 5px rgba(0,0,0,0.04);"
                    onmouseover="this.style.background='#6e8efb';this.style.color='#fff';this.style.borderColor='#6e8efb';this.style.transform='translateY(-2px)';"
                    onmouseout="this.style.background='#fff';this.style.color='#444';this.style.borderColor='#e5e5e5';this.style.transform='translateY(0)';"
                >${city}</a>`;
            });
        });

        container.innerHTML = outputHTML;
    }
});


var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
    var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
    s1.async=true;
    s1.src='https://embed.tawk.to/6a3cdccd2a464c1d44a16197/default';
    s1.charset='UTF-8';
    s1.setAttribute('crossorigin','*');
    s0.parentNode.insertBefore(s1,s0);
})();