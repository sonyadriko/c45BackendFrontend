<!doctype html>
<html lang="en">

<head>
    <title>Dashboard</title>
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
                <div class="theory">
                    <h2>Algoritma C4.5</h2>
                    <p>
                        Algoritma C4.5 adalah metode untuk membangun pohon keputusan yang dikembangkan oleh Ross Quinlan
                        sebagai penyempurnaan dari algoritma ID3. Algoritma ini memilih atribut yang paling efektif
                        dalam
                        memisahkan data berdasarkan Gain Ratio, membagi dataset berdasarkan nilai atribut tersebut, dan
                        mengulangi proses ini untuk setiap subset data hingga pohon selesai. C4.5 dapat menangani
                        atribut
                        kontinu, missing values, dan menerapkan pruning untuk mengurangi overfitting, menjadikannya
                        lebih
                        robust dibandingkan ID3. Meskipun demikian, C4.5 bisa menjadi lambat dan membutuhkan banyak
                        memori
                        untuk dataset yang sangat besar.
                    </p>
                </div>
                <!-- <div class="row row-cols-1 row-cols-lg-2 row-cols-xxl-4">
                    <div class="col">
                        <div class="card radius-10">
                            <div class="card-body">
                                <div class="d-flex align-items-start gap-2">
                                    <div>
                                        <p class="mb-0 fs-6">Total Revenue</p>
                                    </div>
                                    <div class="ms-auto widget-icon-small text-white bg-gradient-purple">
                                        <ion-icon name="wallet-outline"></ion-icon>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mt-3">
                                    <div>
                                        <h4 class="mb-0">$92,854</h4>
                                    </div>
                                    <div class="ms-auto">+6.32%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-10">
                            <div class="card-body">
                                <div class="d-flex align-items-start gap-2">
                                    <div>
                                        <p class="mb-0 fs-6">Total Customer</p>
                                    </div>
                                    <div class="ms-auto widget-icon-small text-white bg-gradient-info">
                                        <ion-icon name="people-outline"></ion-icon>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mt-3">
                                    <div>
                                        <h4 class="mb-0">48,789</h4>
                                    </div>
                                    <div class="ms-auto">+12.45%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-10">
                            <div class="card-body">
                                <div class="d-flex align-items-start gap-2">
                                    <div>
                                        <p class="mb-0 fs-6">Total Orders</p>
                                    </div>
                                    <div class="ms-auto widget-icon-small text-white bg-gradient-danger">
                                        <ion-icon name="bag-handle-outline"></ion-icon>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mt-3">
                                    <div>
                                        <h4 class="mb-0">88,234</h4>
                                    </div>
                                    <div class="ms-auto">+3.12%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-10">
                            <div class="card-body">
                                <div class="d-flex align-items-start gap-2">
                                    <div>
                                        <p class="mb-0 fs-6">Conversion Rate</p>
                                    </div>
                                    <div class="ms-auto widget-icon-small text-white bg-gradient-success">
                                        <ion-icon name="bar-chart-outline"></ion-icon>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mt-3">
                                    <div>
                                        <h4 class="mb-0">48.76%</h4>
                                    </div>
                                    <div class="ms-auto">+8.52%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->
                <!--end row-->
            </div>
            <!-- end page content-->
        </div>
        <!--end page content wrapper-->
        <!--start footer-->
        <!--end footer-->
        <!--Start Back To Top Button-->
        <a href="javaScript:;" class="back-to-top">
            <ion-icon name="arrow-up-outline"></ion-icon>
        </a>
        <!--End Back To Top Button-->

        <!--start switcher-->
        <!--end switcher-->
        <!--start overlay-->
        <div class="overlay nav-toggle-icon"></div>
        <!--end overlay-->

    </div>
    <!--end wrapper-->

    <?php include 'footer.php' ?>

    <?php include 'js.php' ?>

</body>

</html>