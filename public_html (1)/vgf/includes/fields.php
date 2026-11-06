<?php
function crf_labels(): array {
    return [
        'contact_name' => 'Contact name',
        'contact_email' => 'Email',
        'contact_phone' => 'WhatsApp / phone',
        'contact_role' => 'Role',
        'q1_company' => '1. Parent company',
        'q1_company_other' => '1. Other company name',
        'q2_domain_status' => '2. Domain status',
        'q2_domain_name' => '2. Existing domain',
        'q2_domain_owner' => '2. Domain owner',
        'q3_preferred_domain' => '3. Preferred domain',
        'q4_decision_maker' => '4. Decision-maker',
        'q5_businesses' => '5. Businesses to include',
        'q5_other' => '5. Other business',
        'q6_business_details' => '6. Business details',
        'q7_legal' => '7. Legal structure',
        'q8_branding' => '8. Branding',
        'q9_architecture' => '9. Visitor experience / architecture',
        'q10_urls' => '10. URL structure',
        'q11_homepage' => '11. Group homepage first?',
        'q12_recommend' => '12. Launch recommendation',
        'q13_existing' => '13. Existing websites',
        'q14_old_sites' => '14. Old sites action',
        'q15_access' => '15. Domain / hosting access',
        'q16_assets' => '16. Assets available',
        'q17_copy' => '17. Who writes copy',
        'q18_references' => '18. Reference websites',
        'q19_group_pages' => '19. Group pages',
        'q19_other' => '19. Other group pages',
        'q20_company_pages' => '20. Company pages',
        'q21_features' => '21. Features',
        'q22_booking' => '22. Booking / lead needs',
        'q23_country' => '23. Country',
        'q23_cities' => '23. Cities / service areas',
        'q24_gbp' => '24. Google Business Profiles',
        'q24_gbp_links' => '24. GBP links',
        'q25_keywords' => '25. Priority search terms',
        'q26_ga' => '26. GA4 / Search Console',
        'q27_seo_scope' => '27. SEO scope',
        'q28_hosting' => '28. Hosting',
        'q28_emails' => '28. Business emails',
        'q28_keep_email' => '28. Keep emails unchanged?',
        'q29_manage' => '29. Who manages after launch',
        'q30_timeline' => '30. Timeline',
        'q30_date' => '30. Specific date',
        'q31_package' => '31. Package to quote',
        'q32_budget' => '32. Budget',
        'q33_notes' => '33. Notes',
    ];
}

function collect_form(array $post): array {
    $multi = ['q5_businesses','q16_assets','q19_group_pages','q20_company_pages','q21_features'];
    $data = [];
    foreach (array_keys(crf_labels()) as $key) {
        if (in_array($key, $multi, true)) {
            $data[$key] = array_values(array_filter((array)($post[$key] ?? []), 'strlen'));
        } else {
            $data[$key] = trim((string)($post[$key] ?? ''));
        }
    }
    return $data;
}
?>
