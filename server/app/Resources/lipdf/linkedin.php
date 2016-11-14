<?php

use GPSPDF as G;

//
// Called once per "line" of the PDF
// Each line contains some text with accompanying metadata (font size, position in PDF file etc)
// helpers.php contains convenience functions for pulling out this metadata.
// The best way to check font sizes in a PDF is to manually run
// `pdf2txt -t xml path.pdf` and compare 'size' in the XML output.
//

// Name
if (G\fontSize() == 23.060 && G\position() < 2) {
    G\mark('name');

    G\output('name', G\contents());
}

// Headline / Email address
if (G\fontSize() == 14.508 && G\withinDistance('name', 1)) {
    $lines = explode("\n", G\contents());

    G\output('headline', $lines[0]);
    G\output('email', $lines[1]);
}

// Summary title
if (G\fontSize() == 17.856 && G\contains('Summary')) {
    G\mark('summary');
}

// Summary paragraph
if (G\fontSize() == 13.392 && G\withinDistance('summary', 1)) {
    G\output('summary', G\contents());
}

// Interests title
if (G\fontSize() == 17.856 && G\contains('Interests')) {
    G\mark('interests');
}

// Interests
if (G\fontSize() == 13.392 && G\withinDistance('interests', 1)) {
    G\mark('interests');

    $interests = explode(',', G\contents());

    foreach ($interests as &$interest) {
        $interest = trim($interest, ' .');
    }

    G\output('interests', $interests);
}

// Experience title
if (G\fontSize() == 17.856 && G\contains('Experience')) {
    G\mark('experience_title');

    $experience = array();
    G\output('experience', array());
}

// Experience - Position / Company
if (G\fontSize() == 13.836 && (G\withinDistance('experience_title', 1) || (G\withinDistance('experience_summary', 1)))) {
    G\mark('experience_position');

    $match = G\match('/(.*) at (.*)/'); // Position  at  Company
    $experience['position'] = trim($match[1]);
    $experience['company'] = trim($match[2]);
}

// Experience - Timeframe / Summary
if (G\fontSize() == 13.392 && G\withinDistance('experience_position', 1)) {
    // Summary
    G\mark('experience_summary');

    $contents = explode("\n", G\contents());
    $timeframe = array_shift($contents);
    $timeframeMatch = G\match('/(.*)\s+-\s+(.*) \(.*\)/');
    $experience['timeframe'] = array(
        'start' => isset($timeframeMatch[1]) ? G\parseDate($timeframeMatch[1]) : null,
        'end' => isset($timeframeMatch[2]) ? G\parseDate($timeframeMatch[2]) : null,
    );
    $experience['summary'] = implode("\n", $contents);

    G\output('experience', $experience);
}

// Organisations title
if (G\fontSize() == 17.856 && G\contains('Organizations')) {
    G\mark('organization');

    G\output('organizations', array());
}

// Organisation
if (G\fontSize() == 13.836 && G\withinDistance('organization', 1)) {
    G\mark('organization');

    $organization = G\contents();
}

// Organisation - Timeframe
if (G\fontSize() == 13.392 && G\withinDistance('organization', 1)) {
    G\mark('organization');

    G\output('organizations', array(
        'organization' => $organization,
        'timeframe' => G\contents(),
    ));
}

// Education title
if (G\fontSize() == 17.856 && G\contains('Education')) {
    G\mark('education_title');

    G\output('education', array());
}

// Education - Provider
if (G\fontSize() == 13.836 && (G\withinDistance('education_title', 1) || G\withinDistance('education', 1))) {
    G\mark('education');

    $education_provider = G\contents();
}

// Education - Description
if (G\fontSize() == 13.392 && G\withinDistance('education', 1)) {
    G\mark('education');

    // Parse out extra detail
    $contents = G\contents();
    $parts = explode(', ', $contents); // Looks like degree, field of study, and timeframe are separated by commas
    $timeframe = G\match('/(\d+)\s+-\s+(\d+|Present)/'); // i.e. 1998 - 2008
    $activities = G\match('/Activities and Societies:\s+(.*)$/');

    G\output('education', array(
        'provider' => $education_provider,
        'degree' => (count($parts) > 2) ? $parts[0] : null,
        'field_of_study' => (count($parts) > 2) ? $parts[1] : null,
        'activities_and_studies' => isset($activities[1]) ? explode(', ', $activities[1]) : null,
        'timeframe' => array(
            'start' => isset($timeframe[1]) ? G\parseDate($timeframe[1]) : null,
            'end' => isset($timeframe[2]) ? G\parseDate($timeframe[2]) : null,
        ),
        'raw' => $contents,
    ));
}

// Projects title
if (G\fontSize() == 17.856 && G\contains('Projects')) {
    G\mark('projects_title');

    G\output('projects', array());
}

// Projects - Name
if (G\fontSize() == 13.836 && (G\withinDistance('projects_title', 1) || G\withinDistance('projects', 1))) {
    G\mark('projects');

    $projects_name = G\contents();
}

// Projects - Description
if (G\fontSize() == 13.392 && G\withinDistance('projects', 1)) {
    G\mark('projects');

    G\output('projects', array(
        'name' => $projects_name,
        'description' => G\contents(),
    ));
}

// Languages title
if (G\fontSize() == 17.856 && G\contains('Languages')) {
    G\mark('languages');

    G\output('languages', array());

    $languages = array();
}

// Language - Language
if (G\fontSize() == 13.836 && G\withinDistance('languages', 1)) {
    G\mark('languages');

    $languages['language'] = explode("\n", G\contents());
}

// Language - Proficiency
if (G\fontSize() == 13.392 && G\contains('proficiency)')) {
    G\mark('languages');

    $languages['proficiency'] = explode("\n", G\contents());

    foreach ($languages['language'] as $key => $language) {
        G\output('languages', array(
            'language' => $languages['language'][$key],
            'proficiency' => $languages['proficiency'][$key],
        ));
    }
}

// Skills & Expertise - Title
if (G\fontSize() == 17.856 && G\contains('Skills & Expertise')) {
    G\mark('skills');
}

// Skills & Expertise - Skills
if (G\fontSize() == 13.836 && G\withinDistance('skills', 1)) {
    // Likely part of skills
    G\mark('skills');

    G\output('skills', explode("\n", G\contents()));
}
