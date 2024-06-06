<?php 
include '../config/database.php';

require '../vendor/autoload.php'; // Make sure this path is correct

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['import'])) {
        // Process the uploaded Excel file for import
        if (isset($_FILES['excelFile'])) {
            $fileError = $_FILES['excelFile']['error'];
            if ($fileError == UPLOAD_ERR_OK) {
                $excelFile = $_FILES['excelFile']['tmp_name'];

                // Save the uploaded Excel file to the backend folder with the name 'data100.xlsx'
                $backendExcelFile = '../../backend/data100.xlsx';
                if (move_uploaded_file($excelFile, $backendExcelFile)) {

                    try {
                        // Load the Excel file
                        $spreadsheet = IOFactory::load($backendExcelFile);
                        $worksheet = $spreadsheet->getActiveSheet();

                        // Prepare the statement for inserting data into the 'data_training' table
                        $stmtInsert = $conn->prepare('INSERT INTO data_training (`service`, `spkts`, `sbytes`, `sttl`, `smean`, `attack_cat`) VALUES (?, ?, ?, ?, ?, ?)');

                        // Initialize variable for counting successful inserts
                        $successCount = 0;

                        // Skip the first row (headers)
                        $rowIterator = $worksheet->getRowIterator(2);
                        $rowIterator->next();

                        // Iterate through rows starting from the second row and insert data into the 'data_training' table
                        foreach ($rowIterator as $row) {
                            $rowData = [];
                            foreach ($row->getCellIterator() as $cell) {
                                $rowData[] = $cell->getValue();
                            }

                            // Assuming the Excel columns are in the order specified
                            if (count($rowData) == 7) { // Ensure the number of columns matches
                                // Extracting values, skipping the first column (no.)
                                $service = $rowData[1];
                                $spkts = $rowData[2];
                                $sbytes = $rowData[3];
                                $sttl = $rowData[4];
                                $smean = $rowData[5];
                                $attack_cat = $rowData[6];

                                // Insert data into the 'data_training' table
                                $stmtInsert->bind_param('ssssss', $service, $spkts, $sbytes, $sttl, $smean, $attack_cat);
                                if ($stmtInsert->execute()) {
                                    $successCount++; // Increment the count of successful inserts
                                }
                            }
                        }

                        // Check if any data was successfully inserted
                        if ($successCount > 0) {
                            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
                            echo "<script>
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Import successful!',
                                        text: 'Total $successCount rows inserted.',
                                        showConfirmButton: true
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            window.location.href = 'data.php';
                                        }
                                    });
                                  </script>";
                        } else {
                            echo '<script>alert("No data inserted.");</script>';
                        }

                    } catch (Exception $e) {
                        echo '<script>alert("Error processing the file: ' . $e->getMessage() . '");</script>';
                    }
                } else {
                    echo '<script>alert("Error moving the uploaded file.");</script>';
                }
            } else {
                echo '<script>alert("Error uploading the file.");</script>';
            }
        } else {
            echo '<script>alert("No file uploaded.");</script>';
        }
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <title>Data Training</title>
    <?php include 'scripts.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

                <h6 class="mb-0 text-uppercase">Data Training</h6>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
                            enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="excelFile" class="form-label">Choose Excel File</label>
                                <input type="file" class="form-control" id="excelFile" name="excelFile"
                                    accept=".xls, .xlsx" required>
                                <div id="fileError" class="invalid-feedback"></div>
                            </div>
                            <button type="submit" class="btn btn-primary" name="import">Upload</button>
                        </form>
                    </div>
                </div>
                <hr />
                <a href="tambah_data_training.php" class="btn btn-primary btn-user">Tambah Data Training</a>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example" class="table table-striped table-bordered" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>service</th>
                                        <th>spkts</th>
                                        <th>sbytes</th>
                                        <th>sttl</th>
                                        <th>smean</th>
                                        <th>attack cat</th>
                                        <th>aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    $get_data = mysqli_query($conn, "select * from data_training");
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
                                        <td>
                                            <a href='edit_data.php?GetID=<?php echo $id; ?>'
                                                style="text-decoration: none; list-style: none;"><input type='submit'
                                                    value='Ubah' id='editbtn' class="btn btn-primary btn-user"></a>
                                            <a href='delete_data.php?Del=<?php echo $id; ?>'
                                                style="text-decoration: none; list-style: none;"><input type='submit'
                                                    value='Hapus' id='delbtn' class="btn btn-primary btn-user"></a>
                                        </td>
                                    </tr>

                                    <?php $no++; } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <form id="clearAllForm" action="clear_all_data.php" method="post">
                                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete All
                                        Data</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
        function confirmDelete() {
            Swal.fire({
                title: 'Delete All Data',
                text: 'Are you sure you want to delete all data?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('clearAllForm').submit();
                }
            });
        }
        </script>
        <a href="javaScript:;" class="back-to-top">
            <ion-icon name="arrow-up-outline"></ion-icon>
        </a>
        <div class="overlay nav-toggle-icon"></div>
        <?php include 'footer.php' ?>

    </div>


    <!-- JS Files-->
    <?php include 'js.php' ?>

</body>

</html>