<?php 
include '../config/database.php'; ?>
<!doctype html>
<html lang="en">

<head>
    <title>Tree Mining</title>
    <?php include 'scripts.php'; ?>
</head>

<body>


    <!--start wrapper-->
    <div class="wrapper">
        <!--start sidebar -->
        <?php include 'sidebar.php'; ?>
        <!--end sidebar -->

        <!--start top header-->
        <?php include 'header.php'; ?>
        <!--end top header-->

        <!-- start page content wrapper-->
        <div class="page-content-wrapper">
            <!-- start page content-->
            <div class="page-content">
                <?php
                ini_set('display_errors', 1);
                error_reporting(E_ALL);

                require '../vendor/autoload.php'; // Make sure this path is correct

                use C45\C45;

                $data_training = [];
                $result = $conn->query("SELECT service, spkts, sbytes, sttl, smean, attack_cat FROM data_training");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Convert spkts data to category based on the value
                        $row['spkts'] = $row['spkts'] <= 10 ? 'low' : 'high';
                        $row['sbytes'] = $row['sbytes'] <= 768 ? 'low' : 'high';
                        $row['sttl'] = $row['sttl'] == 31 ? 'low' : ($row['sttl'] == 62 ? 'med' : 'high');
                        $row['smean'] = $row['smean'] <= 78 ? 'low' : 'high';
                        $data_training[] = $row;
                    }

                    $c45 = new Algorithm\C45();
                    $input = new Algorithm\C45\DataInput;
                    $input->setData($data_training);
                    $input->setAttributes(array('service', 'spkts', 'sbytes', 'sttl', 'smean', 'attack_cat'));
                    $c45->c45 = $input; // Set input data
                    $c45->setTargetAttribute('attack_cat');
                    $initialize = $c45->initialize();
                    
                    echo "<pre>";
                    if (!empty($data_training)) {
                        $datal = $initialize->buildTree()->toString();
                        print_r($datal);
                        $datall = json_encode($datal);
                    } else {
                        echo "No data available for tree mining";
                    }
                    echo "</pre>";
                } else {
                    echo "<p style='font-weight: bold;'>Database kosong, isi atau upload excel data training.</p>";
                }
                ?>
            </div>
            <!-- end page content-->
        </div>
        <!--end page content wrapper-->

        <!--Start Back To Top Button-->
        <a href="javaScript:;" class="back-to-top">
            <ion-icon name="arrow-up-outline"></ion-icon>
        </a>
        <!--End Back To Top Button-->

        <!--start overlay-->
        <div class="overlay nav-toggle-icon"></div>
        <!--end overlay-->
        <?php include 'footer.php'; ?>

    </div>
    <!--end wrapper-->
    <?php include 'js.php' ?>

</body>

</html>