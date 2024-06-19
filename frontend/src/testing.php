<?php 
include '../config/database.php';
?>
<!doctype html>
<html lang="en">

<head>
    <title>Pengujian</title>
    <?php include 'scripts.php'; ?>
</head>

<body>


    <!--start wrapper-->
    <div class="wrapper">

        <!--start sidebar -->
        <?php include 'sidebar.php' ?>
        <!--end sidebar -->

        <!--start top header-->
        <?php include 'header.php' ?>
        <!--end top header-->


        <!-- start page content wrapper-->
        <div class="page-content-wrapper">
            <!-- start page content-->
            <div class="page-content">

                <h6 class="mb-0 text-uppercase">Testing</h6>
                <hr />
                <form method="post" action="">
                    <label for="test_size">Select Test Size:</label>
                    <select id="test_size" name="test_size" class="form-select mt-2">
                        <?php for ($i = 1; $i <= 9; $i++) : ?>
                        <option value="<?php echo $i / 10; ?>">
                            <?php echo "Data Testing " . $i / 10 . " - Data Training " . (1 - $i / 10); ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="btn btn-primary mt-2 mb-2">Submit</button>
                </form>
                <!-- <img src="../backend/tree_graph.png"> -->


                <?php
// URL of the Flask API
$url = 'http://localhost:5000/train_full';

// Data to be sent to the Flask API
$data = array('data_path' => 'data100.xlsx', 'model_path' => 'model.pkl');

// Prepare the POST options
$options = array(
    'http' => array(
        'header'  => "Content-Type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
    )
);

// Create the context for the POST request
$context  = stream_context_create($options);

// Attempt to receive a response from the Flask API
// $result = file_get_contents($url, false, $context);

// // Check the result and decode the JSON response
// if ($result === FALSE) {
//     // If the server is unreachable or returns a bad response, output an error message
//     echo "Failed to connect to the Flask server or received a bad response.";
// } else {
//     // Decode the JSON response
//     $response = json_decode($result);

//     // Check if there was a JSON decoding error
//     if (json_last_error() !== JSON_ERROR_NONE) {
//         // Output the JSON error message
//         echo "JSON decoding error: " . json_last_error_msg();
//     } elseif (isset($response->error)) {
//         // If the Flask API returned an error, display it
//         echo "Error from Flask: " . $response->error;
//     } elseif (isset($response->image_url)) {
//         // If the response includes an image URL, display the image
//         echo "<img src='" . htmlspecialchars($response->image_url) . "' alt='Model Diagram'>";
//     } else {
//         // If the response format is unexpected, output the raw response for debugging
//         echo "Unexpected response format: " . htmlspecialchars($result);
//     }
// }
?>
                <?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $test_size = $_POST['test_size'];
    $endpoint = "http://localhost:5000/testc45?test_size=$test_size";

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    $data = json_decode($response, true);

    // Validasi respons dari API
    if (is_array($data) && isset($data['confusion_details'], $data['classification_report'])) {
        // Tampilkan informasi
      
        echo "<h2>Confusion Matrix:</h2>";
        echo "<div class='confusion-matrix'>";
        foreach ($data['confusion_matrix'] as $row) {
            echo "<div class='confusion-row'>" . implode(' ', $row) . "</div>";
        }
        echo "</div>";
        // // Tampilkan TN, FP, FN, TP jika tersedia
        // foreach ($data['confusion_details'] as $class => $details) {
        //     echo "<h3>$class:</h3>";
        //     echo "TP: " . $details['TP'] . "<br>";
        //     echo "TN: " . $details['TN'] . "<br>";
        //     echo "FP: " . $details['FP'] . "<br>";
        //     echo "FN: " . $details['FN'] . "<br>";
        // }

        // echo "Classification Report: <pre>" . $data['classification_report'] . "</pre><br>";
        echo "<strong>Accuracy:</strong> " . number_format($data['accuracy'], 3) . "<br>";
        echo "<strong>Precision:</strong> " . number_format($data['precision'], 3) . "<br>";
        echo "<strong>F1 Score:</strong> " . number_format($data['f1_score'], 3) . "<br>";
        echo "<strong>Recall:</strong> " . number_format($data['recall'], 3) . "<br>";
        

    } else {
        echo ' Data yang diterima dari API tidak lengkap atau tidak valid.';
    }
}
?>

                <!--end row-->


                <!--end row-->
            </div>
            <!-- end page content-->
        </div>
        <!--Start Back To Top Button-->
        <a href="javaScript:;" class="back-to-top">
            <ion-icon name="arrow-up-outline"></ion-icon>
        </a>
        <div class="overlay nav-toggle-icon"></div>
        <!--end overlay-->
        <?php include 'footer.php' ?>

    </div>
    <!--end wrapper-->


    <?php include 'js.php' ?>


</body>

</html>