<?php 
include '../config/database.php'; ?>
<?php
include '../config/database.php';

// Ambil data dari tabel MySQL
$sql = 'SELECT * FROM data_training';
$result = $conn->query($sql);

// Persiapkan data untuk diproses oleh algoritma C4.5
$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Hitung jumlah rekaman total
$total_records = count($data);

// Hitung jumlah rekaman dalam setiap kelas
$class_counts = array_count_values(array_column($data, 'attack_cat'));

// Hitung entropi dataset
$entropy = 0;
foreach ($class_counts as $class_count) {
    $probability = $class_count / $total_records;
    $entropy -= $probability * log($probability, 2);
}

$exploits_count = isset($class_counts['Exploits']) ? $class_counts['Exploits'] : 0;
$generic_count = isset($class_counts['Generic']) ? $class_counts['Generic'] : 0;
$fuzzers_count = isset($class_counts['Fuzzers']) ? $class_counts['Fuzzers'] : 0;
$normal_count = isset($class_counts['Normal']) ? $class_counts['Normal'] : 0;

// Calculate the probability of each attack category
$exploits_probability = $exploits_count / $total_records;
$generic_probability = $generic_count / $total_records;
$fuzzers_probability = $fuzzers_count / $total_records;
$normal_probability = $normal_count / $total_records;

// Calculate the entropy contribution for each attack category
$exploits_entropy = $exploits_count > 0 ? -$exploits_probability * log($exploits_probability, 2) : 0;
$generic_entropy = $generic_count > 0 ? -$generic_probability * log($generic_probability, 2) : 0;
$fuzzers_entropy = $fuzzers_count > 0 ? -$fuzzers_probability * log($fuzzers_probability, 2) : 0;
$normal_entropy = $normal_count > 0 ? -$normal_probability * log($normal_probability, 2) : 0;

// Total entropy calculation
$total_entropy = $exploits_entropy + $generic_entropy + $fuzzers_entropy + $normal_entropy;

// Hitung statistik untuk setiap fitur
$statistics = [
    'service' => [],
    'spkts' => [],
    'sbytes' => [],
    'sttl' => [],
    'smean' => [],
    'null' => [],
];

foreach ($statistics as $feature => &$values) {
    $total_gain_attribute = 0;
    $unique_values = array_unique(array_column($data, $feature));
    if ($feature == 'spkts') {
        // Kelompokkan spkts menjadi <=12 dan >12
        $unique_values = ['<=12', '>12'];
    } elseif ($feature == 'sbytes') {
        // Kelompokkan sbytes menjadi <=766 dan >766
        $unique_values = ['<=826', '>826'];
    } elseif ($feature == 'sttl') {
        // Kelompokkan sttl menjadi 31, 62, dan 254
        $unique_values = [31, 62, 254];
    } elseif ($feature == 'smean') {
        // Kelompokkan smean menjadi <=78 dan >78
        $unique_values = ['<=78', '>78'];
    } else {
        $unique_values = array_unique(array_column($data, $feature));
    }

    foreach ($unique_values as $value) {
        if ($feature === 'null') {
            continue; // Skip null feature
        }

        $subset_entropy = 0;
        $value_count = 0;
        $class_counts_subset = [];
        $split_info_subset = 0;

        foreach ($data as $row) {
            $match_condition = false;
            if ($feature == 'spkts') {
                $match_condition = ($value == '<=12' && $row[$feature] <= 12) || ($value == '>12' && $row[$feature] > 12);
            } elseif ($feature == 'sbytes') {
                $match_condition = ($value == '<=826' && $row[$feature] <= 826) || ($value == '>826' && $row[$feature] > 826);
            } elseif ($feature == 'sttl') {
                $match_condition = $row[$feature] == $value;
            } elseif ($feature == 'smean') {
                $match_condition = ($value == '<=78' && $row[$feature] <= 78) || ($value == '>78' && $row[$feature] > 78);
            } else {
                $match_condition = $row[$feature] == $value;
            }

            if ($match_condition) {
                $value_count++;
                if (!isset($class_counts_subset[$row['attack_cat']])) {
                    $class_counts_subset[$row['attack_cat']] = 0;
                }
                $class_counts_subset[$row['attack_cat']]++;
            }
        }

        $gain = 0;
        foreach ($class_counts_subset as $class_count) {
            $probability = $class_count / $value_count;
            $subset_entropy -= $probability * log($probability, 2);

            $subset_probability = $value_count / $total_records;

            $gain_subset = $subset_probability * $subset_entropy;
            $gain += $gain_subset;
        }

        $split_info_subset -= $subset_probability * log($subset_probability, 2);

        $values[] = [
            'Value' => $value,
            'Jml_Record' => $value_count,
            'Fuzzers' => isset($class_counts_subset['Fuzzers']) ? $class_counts_subset['Fuzzers'] : 0,
            'Exploits' => isset($class_counts_subset['Exploits']) ? $class_counts_subset['Exploits'] : 0,
            'Generic' => isset($class_counts_subset['Generic']) ? $class_counts_subset['Generic'] : 0,
            'Normal' => isset($class_counts_subset['Normal']) ? $class_counts_subset['Normal'] : 0,
            'Entropy' => $subset_entropy,
            'Gain' => $gain_subset,
            'Split_Info' => $split_info_subset,
        ];
    }
}

$gain_ratios = [];
$best_attribute = '';

?>
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
                <h3>Total Entropy : <?php echo $total_entropy; ?></h3>

                <?php
                // Tampilkan statistik yang dihitung
                foreach ($statistics as $feature => $values) {
                    if ($feature === 'null') {
                        continue; // Skip null feature
                    }
                    echo "<h6 class='mb-0 text-uppercase'>$feature</h6>";
                    echo "<table border='1'>";
                    echo '<tr><th>Value</th><th>Jml Record</th><th>Fuzzers</th><th>Exploits</th><th>Generic</th><th>Normal</th><th>Entropy</th><th>Gain</th></tr>';
                    foreach ($values as $value) {
                        echo '<tr>';
                        echo "<td>{$value['Value']}</td>";
                        echo "<td>{$value['Jml_Record']}</td>";
                        echo "<td>{$value['Fuzzers']}</td>";
                        echo "<td>{$value['Exploits']}</td>";
                        echo "<td>{$value['Generic']}</td>";
                        echo "<td>{$value['Normal']}</td>";
                        echo "<td>" . number_format($value['Entropy'], 3) . "</td>";
                        echo "<td>" . number_format($value['Gain'], 3) . "</td>";                        
                        echo '</tr>';
                    }
                    $total_gain_attribute = 0;
                    $total_split_info = 0;
                
                    // Calculate total gain for this attribute
                    foreach ($values as $value) {
                        $total_gain_attribute += $value['Gain'];
                        $total_split_info += $value['Split_Info'];
                    }
                    $total_gain = $total_entropy - $total_gain_attribute;
                
                    $gain_ratio_total = $total_gain / $total_split_info;
                
                    $gain_ratios[$feature] = $gain_ratio_total;
                
                    echo '</table>';
                    echo "<td>Gain : " . number_format($total_gain, 3) . "</td>";
                    echo "<br>";
                    echo "<td>Split Ratios : " . number_format($total_split_info, 3) . "</td>";
                    echo "<br>";
                    echo "<td>Gain Ratios : " . number_format($gain_ratio_total, 3) . "</td>";
                    echo '<br>';
                    echo '<br>';
                
                    $best_attribute = array_search(max($gain_ratios), $gain_ratios);
                }
                // echo "The best attribute to split on is: $best_attribute";
                    echo "<h4>Values for Best Attribute ($best_attribute)</h4>";
                
                ?>
                <!-- <table id="example" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>service</th>
                            <th>spkts</th>
                            <th>sbytes</th>
                            <th>sttl</th>
                            <th>smean</th>
                            <th>attack cat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                                    $no = 1;
                                    $get_data = mysqli_query($conn, "select * from data_training where service ='http'");
                                            while($display = mysqli_fetch_array($get_data)) {
                                                $id = $display['id_data_training'];
                                                $service = $display['service'];
                                                $spkts = $display['spkts'];
                                                $sbytes = $display['sbytes'];
                                                $sttl = $display['sttl'];
                                                $smean = $display['smean'];
                                                $attack_cat = $display['attack_cat'];
                                    ?>
                        <tr>
                            <td><?php echo $no; ?></td>
                            <td><?php echo $service; ?></td>
                            <td><?php echo $spkts; ?></td>
                            <td><?php echo $sbytes; ?></td>
                            <td><?php echo $sttl; ?></td>
                            <td><?php echo $smean; ?></td>
                            <td><?php echo $attack_cat; ?></td>

                        </tr>

                        <?php $no++; } ?>
                    </tbody>
                </table> -->
            </div>
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