<?php

$data = array(
    'apiKey' => 'key',
    'formId' => 'id',
    'team' => 'id',
    'reportId' => 'id'
);

// Construct the headers
$headers = array(
    'Content-Type' => 'application/json',
    'jf-team-id' => $data['team'],
    ''
);

// Construct the options array
$options = array(
    'http' => array(
        'method' => 'GET',
        'header' => implode("\r\n", array_map(
            function ($key, $value) {
                return "$key: $value";
            },
            array_keys($headers),
            $headers
        ))
    )
);

// Create a stream context
$context = stream_context_create($options);

// Init variables
$array = [];
$count = 0;

for ($i = 0; $i <= 10000; $i = $i + 250) {
    
    // Construct the URL
    $url = "https://nsight.jotform.com/API/form/{$data['formId']}/submissions?apiKey={$data['apiKey']}&limit=250&offset=$i";

    echo "calling submissions up to $i\n {$url}";

    // Perform the HTTP request
    $response = file_get_contents($url, false, $context);

    // Output the decoded response
    $decoded = json_decode($response);

    if ($decoded && isset($decoded->responseCode) && $decoded->responseCode == 200) {
        $submissions = $decoded->content;
        foreach ($submissions as $submission) {
            
            // create date/time based on the submission date
            $dateTime = new DateTime($submission->created_at);

            // Cleaning the data to avoid problem creating DIR
            $clinic = isset($submission->answers->{135}->answer) ? strtolower(trim(str_replace(['.', ':', "/", ";"], '', $submission->answers->{135}->answer))) : '';
            $other = isset($submission->answers->{174}->answer) ? strtolower(trim(str_replace(['.', ':', "/", ";"], '', $submission->answers->{174}->answer))) : '';
            $name = isset($submission->answers->{177}->answer) ? strtolower(trim(str_replace(['.', ':', "/", ";"], '',($submission->answers->{177}->answer)))) : '';

            // Create object
            $submissionData = array(
                'id' => $submission->id,
                'clinic' => $clinic,
                'other' => $other,
                'name' => $name,
                'monthAndYear' => $dateTime->format("m-Y"),
                'formattedDate' => $dateTime->format("Y-m-d"),
            );

            // Push object to the array
            $array[] = $submissionData;

            // Output or use the extracted data
            echo "Completed submission ID $submission->id\n";

            // Process answers or perform additional tasks based on your needs
        }
    } else {
        echo "Error in the response or response code is not 200.";
    }

}

// Get the total number of submission and init the variable to count the downloaded files
$numberOfSubmissions = sizeof($array);
$numberOfDownloadedFiles = 0;

foreach($array as $submission) {
    $numberOfDownloadedFiles++;
    
    $url = "https://nsight.jotform.com/server.php?action=getSubmissionPDF&sid={$submission['id']}&formID={$data['formId']}&apiKey={$data['apiKey']}";

    if(isset($submission['other']) && !empty($submission['other'] && $submission['clinic'] == 'other')) {
        $clinic = $submission['other'];
    } else {
        $clinic = $submission['clinic'];
    }
    
    $fileName = "{$submission['id']}_{$submission['formattedDate']}_{$clinic}_{$submission['name']}.pdf";
    $current_time = date("Y-m-d H:i:s");

    echo "{$numberOfDownloadedFiles}/{$numberOfSubmissions} Download started at {$current_time} \nFilename {$fileName} \n";

    // Base DIR for 'clinic' and 'monthAndYear'
    $baseDirectory = __DIR__ .  DIRECTORY_SEPARATOR . "pdf" .  DIRECTORY_SEPARATOR;

    // Verify existence and create folder 'clinic'
    $clinicDirectory = $baseDirectory . $clinic . DIRECTORY_SEPARATOR;
    if (!file_exists($clinicDirectory)) {
        mkdir($clinicDirectory, 0777, true);
    }

    // Verify existence and create folder 'monthAndYear' inside the folder 'clinic'
    $monthAndYearDirectory = $clinicDirectory . $submission['monthAndYear'] . DIRECTORY_SEPARATOR;
    if (!file_exists($monthAndYearDirectory)) {
        mkdir($monthAndYearDirectory, 0777, true);
    }

    // Full path to the destination file
    $destinationFilePath = $monthAndYearDirectory . $fileName;

    // Check if the file already exists
    if (file_exists($destinationFilePath)) {
        echo "File already exists. Skipping download.\n";
        echo "----------------SKIPPED----------------\n\n";
        continue; // Move to the next iteration of the loop
    }

    // Download file from URL to the folder 'monthAndYear'
    file_put_contents($monthAndYearDirectory . $fileName, file_get_contents($url));

    echo "----------------COMPLETED----------------\n\n";

}

echo "------------------------ALL FILES DOWNLOADED-------------------------";