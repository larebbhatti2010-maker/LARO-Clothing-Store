<?php
require_once "db.php";

/* =========================================================
   ADD PRODUCT TO ORDERS
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["product"])) {

    $product = trim($_POST["product"] ?? "");
    $color   = trim($_POST["color"] ?? "");

    if ($product !== "") {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO orders (product, color) VALUES (?, ?)"
        );

        if (!$stmt) {
            die("Prepare failed: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param(
            $stmt,
            "ss",
            $product,
            $color
        );

        if (!mysqli_stmt_execute($stmt)) {
            die("Order insert failed: " . mysqli_stmt_error($stmt));
        }

        mysqli_stmt_close($stmt);

        header("Location: newarrival.php?added=1");
        exit;
    }
}


/* =========================================================
   GET NEW PRODUCTS
   ========================================================= */

$products = [];

$result = mysqli_query(
    $conn,
    "SELECT * FROM products
     WHERE is_new = 1
     ORDER BY id ASC"
);

if (!$result) {
    die("Products query failed: " . mysqli_error($conn));
}

while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

mysqli_free_result($result);


/* =========================================================
   FILTER PRODUCTS BY CATEGORY
   ========================================================= */

function products_in($products, $category)
{
    $list = [];

    foreach ($products as $item) {

        if (($item["category"] ?? "") === $category) {
            $list[] = $item;
        }
    }

    return $list;
}


/* =========================================================
   DISPLAY PRODUCT CARDS
   ========================================================= */

function show_cards($items)
{
    if (!$items) {

        echo '
        <p class="text-center no-products">
            No products in this collection yet.
        </p>';

        return;
    }

    echo '
    <div class="container-sm product-container">
        <div class="container text-center">
            <div class="row g-4">
    ';

    foreach ($items as $item) {

        $name = htmlspecialchars(
            $item["name"] ?? "",
            ENT_QUOTES,
            "UTF-8"
        );

        $desc = htmlspecialchars(
            $item["description"] ?? "",
            ENT_QUOTES,
            "UTF-8"
        );

        $img = htmlspecialchars(
            $item["image"] ?? "",
            ENT_QUOTES,
            "UTF-8"
        );

        $age = htmlspecialchars(
            $item["age_range"] ?? "",
            ENT_QUOTES,
            "UTF-8"
        );

        $color = htmlspecialchars(
            $item["color"] ?? "",
            ENT_QUOTES,
            "UTF-8"
        );

        $price = number_format(
            (float)($item["price"] ?? 0),
            2
        );


        echo '
        <div class="col-md-4">

            <div class="card product-card h-100">

                <img
                    src="' . $img . '"
                    class="card-img-top product-image"
                    alt="' . $name . '"
                >

                <div class="card-body text-start">

                    <h5 class="card-title">
                        ' . $name . '
                    </h5>

                    <p class="card-text">
                        ' . $desc . '
                    </p>

                    <p class="mb-1">
                        <span class="price">
                            $' . $price . '
                        </span>
                    </p>

                    <p class="mb-3">
                        <span class="age-badge">
                            ' . $age . '
                        </span>
                    </p>

                    <form method="post">

                        <input
                            type="hidden"
                            name="product"
                            value="' . $name . '"
                        >

                        <input
                            type="hidden"
                            name="color"
                            value="' . $color . '"
                        >

                        <button
                            type="submit"
                            class="btn add-cart-btn"
                        >
                            Add to Cart
                        </button>

                    </form>

                </div>

            </div>

        </div>
        ';
    }

    echo '
            </div>
        </div>
    </div>
    ';
}

?>

<!doctype html>

<html lang="en">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>LARO | New Arrivals</title>


    <!-- =====================================================
         BOOTSTRAP
         ===================================================== -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- =====================================================
         GOOGLE FONTS
         ===================================================== -->

    <style>

        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@400;700&family=Raleway:wght@400;600;700&display=swap');


        /* =====================================================
           GENERAL
           ===================================================== */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Raleway', sans-serif;
            background-color: #ffffff;
            color: #31141A;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
        }

        h2,
        h3,
        h4,
        h5,
        a {
            font-family: 'Raleway', sans-serif;
            font-weight: 600;
        }

        p,
        button {
            font-family: 'Inter', sans-serif;
        }


        /* =====================================================
           TOP BAR
           ===================================================== */

        .top-bar {

            width: 100%;

            display: flex;

            justify-content: space-between;

            align-items: center;

            background-color: #31141A;

            color: white;

            padding: 8px 15px;

            gap: 15px;

        }

        .top-bar p {

            margin: 0;

            font-size: 13px;

        }

        .icons img {

            width: 30px;

        }


        /* =====================================================
           NAVBAR
           ===================================================== */

        .navbar {

            background-color: white;

        }

        .container a img {

            width: 80px;

        }

        .nav-item a {

            font-weight: 600;

            color: #31141A;

        }

        .nav-item a:hover {

            color: #6b303b;

        }

        .navbar .dropdown-item {

            color: #4a3838;

            font-size: 14px;

            padding: 10px 15px;

            border-radius: 8px;

        }

        .navbar .dropdown-item:hover {

            background: #31141A;

            color: white;

        }

        @media (min-width: 992px) {

            .dropdown:hover .dropdown-menu {

                display: block;

                margin-top: 0;

            }

        }


        /* =====================================================
           HERO
           ===================================================== */

        .hero-section {

            border: none;

            margin-top: 30px;

            position: relative;

        }

        .hero-image {

            width: 100%;

            height: 96vh;

            object-fit: cover;

        }

        .hero-overlay {

            margin-top: 40px;

            margin-left: 50px;

        }

        .hero-small-title {

            color: white;

            font-size: 20px;

            font-weight: 600;

        }

        .hero-title {

            font-size: 80px;

            line-height: 1.05;

            color: #31141A;

        }

        .hero-subtitle {

            font-size: 20px;

            color: white;

            margin-top: 20px;

        }


        /* =====================================================
           SUCCESS MESSAGE
           ===================================================== */

        .success-message {

            margin-top: 20px;

            margin-bottom: 20px;

            text-align: center;

        }


        /* =====================================================
           MAIN COLLECTION TITLE
           ===================================================== */

        .main-title {

            text-align: center;

            margin-top: 40px;

            color: #31141A;

        }


        /* =====================================================
           COLLECTION CATEGORY CARDS
           ===================================================== */

        .collection-container {

            display: flex;

            justify-content: center;

            gap: 30px;

            margin-top: 30px;

            flex-wrap: wrap;

        }

        .collection-card {

            width: 18rem;

            min-height: 180px;

            background-size: cover;

            background-position: center;

            border: none;

            overflow: hidden;

        }

        .collection-card .card-header {

            background-color: rgba(255,255,255,0.90);

            color: #31141A;

            font-weight: 600;

        }

        .collection-card .card-body {

            background: rgba(255,255,255,0.85);

        }

        .collection-card h5 {

            color: #31141A;

        }


        /* =====================================================
           BUTTONS
           ===================================================== */

        .explore-btn {

            background-color: #31141A;

            color: white;

            border: none;

        }

        .explore-btn:hover {

            background-color: #4b1e27;

            color: white;

        }

        .add-cart-btn {

            background-color: #31141A;

            color: white;

            border: none;

            padding: 10px 18px;

        }

        .add-cart-btn:hover {

            background-color: #4b1e27;

            color: white;

        }


        /* =====================================================
           COLLECTION HEADINGS
           ===================================================== */

        .collection-heading {

            color: #31141A;

            text-align: center;

            margin-top: 80px;

            margin-bottom: 10px;

        }

        #T-SHIRT {

            margin-top: 20px;

        }


        /* =====================================================
           PRODUCTS
           ===================================================== */

        .product-container {

            margin-top: 20px;

            margin-bottom: 40px;

        }

        .product-card {

            border: 1px solid #eeeeee;

            border-radius: 8px;

            overflow: hidden;

            transition: all 0.3s ease;

        }

        .product-card:hover {

            transform: translateY(-5px);

            box-shadow:
                0 8px 25px
                rgba(49, 20, 26, 0.15);

        }

        .product-image {

            width: 100%;

            height: 350px;

            object-fit: cover;

        }

        .product-card .card-body {

            padding: 20px;

        }

        .product-card .card-title {

            color: #31141A;

            font-weight: 700;

        }

        .product-card .card-text {

            color: #555;

            min-height: 45px;

        }

        .price {

            color: #31141A;

            font-size: 20px;

            font-weight: 700;

        }

        .age-badge {

            display: inline-block;

            background-color: #31141A;

            color: white;

            padding: 5px 10px;

            border-radius: 5px;

            font-size: 13px;

        }

        .no-products {

            margin-top: 20px;

            margin-bottom: 40px;

            color: #777;

        }


        /* =====================================================
           FOOTER
           ===================================================== */

        .FOOTER {

            background-color: #31141A;

            color: #fff;

            display: flex;

            flex-wrap: wrap;

            justify-content: space-between;

            gap: 30px;

            padding: 50px 5%;

            margin-top: 60px;

        }

        .FOOTER-BOX {

            flex: 1 1 200px;

            min-width: 200px;

        }

        .FOOTER-BOX h2,
        .FOOTER-BOX h3 {

            color: white;

        }

        .FOOTER-BOX p {

            color: #f0f0f0;

            line-height: 1.7;

        }

        .FOOTER a {

            display: block;

            color: #f0f0f0;

            text-decoration: none;

            margin: 6px 0;

        }

        .FOOTER a:hover {

            color: white;

            text-decoration: underline;

        }

        .COPYRIGHT {

            background-color: #220e14;

            color: #fff;

            text-align: center;

            padding: 15px;

        }

        .COPYRIGHT p {

            margin: 0;

        }


        /* =====================================================
           MOBILE
           ===================================================== */

        @media (max-width: 768px) {

            .top-bar {

                flex-direction: column;

                text-align: center;

                padding: 12px;

            }

            .top-bar p {

                font-size: 12px;

            }

            .navbar {

                margin-top: 10px !important;

            }

            .navbar-collapse {

                margin-left: 0 !important;

            }

            .navbar-nav {

                margin-top: 10px !important;

            }

            .hero-image {

                height: 70vh;

            }

            .hero-overlay {

                margin-top: 20px;

                margin-left: 20px;

            }

            .hero-small-title {

                font-size: 16px;

            }

            .hero-title {

                font-size: 45px;

            }

            .hero-subtitle {

                font-size: 16px;

            }

            .collection-card {

                width: 100%;

                max-width: 18rem;

            }

            .product-image {

                height: 300px;

            }

            .collection-heading {

                font-size: 32px;

            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     TOP BAR
     ========================================================= -->

<div class="top-bar">

    <p>
        Free Shipping on Orders Over ₨5,000
    </p>

    <p>
        7-Day Easy Returns
    </p>

    <p>
        Exclusive Styles — Join LARO
    </p>

    <div class="icons">

        <a href="#">

            <img
                src="search.png"
                width="30"
                alt="Search"
            >

        </a>

    </div>

</div>


<!-- =========================================================
     NAVIGATION
     ========================================================= -->

<nav
    class="navbar navbar-expand-lg"
    style="margin-top: 40px;"
>

    <div class="container">


        <!-- LOGO -->

        <a
            class="navbar-brand"
            href="index.html"
        >

            <img
                src="l2.png"
                alt="LARO Logo"
            >

        </a>


        <!-- MOBILE MENU BUTTON -->

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNavDropdown"
            aria-controls="navbarNavDropdown"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >

            <span class="navbar-toggler-icon"></span>

        </button>


        <!-- NAVIGATION LINKS -->

        <div
            class="collapse navbar-collapse"
            id="navbarNavDropdown"
            style="margin-left: 41px;"
        >

            <ul
                class="navbar-nav"
                style="margin-top: 20px; gap: 5px;"
            >

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="index.html"
                    >
                        HOME
                    </a>

                </li>


                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="shop.html"
                    >
                        SHOP
                    </a>

                </li>


                <li class="nav-item">

                    <a
                        class="nav-link active"
                        href="newarrival.php"
                    >
                        NEW ARRIVALS
                    </a>

                </li>


                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="dashboard.php"
                    >
                        DASHBOARD
                    </a>

                </li>

            </ul>

        </div>

    </div>

</nav>


<!-- =========================================================
     HERO BANNER
     ========================================================= -->

<div class="card text-bg-dark hero-section">

    <img
        src="ARRAIVALBANNAR2.jpg"
        class="card-img hero-image"
        alt="New Arrivals Banner"
    >

    <div
        class="card-img-overlay hero-overlay"
    >

        <h5 class="hero-small-title">
            NEW ARRIVALS
        </h5>


        <h1 class="card-title hero-title">

            DRESS<br>
            YOUR<br>
            MOMENT.

        </h1>


        <p class="card-text hero-subtitle">

            TIMELESS PIECES<br>
            MADE FOR YOU.

        </p>

    </div>

</div>


<!-- =========================================================
     SUCCESS MESSAGE
     ========================================================= -->

<?php if (isset($_GET["added"])): ?>

    <div
        class="alert alert-success success-message container"
    >

        Product added to orders successfully.

    </div>

<?php endif; ?>


<!-- =========================================================
     MAIN TITLE
     ========================================================= -->

<h1 class="main-title">

    NEW ARRIVAL COLLECTION

</h1>


<!-- =========================================================
     CATEGORY CARDS
     ========================================================= -->

<div class="container collection-container">


    <!-- T-SHIRTS -->

    <div
        class="card mb-3 collection-card"
        style="background-image:url('P (2).jpg');"
    >

        <div class="card-header">

            NEW ARRIVAL

        </div>

        <div class="card-body">

            <h5>
                T-Shirts
            </h5>

            <a href="#T-SHIRT">

                <button
                    class="btn explore-btn"
                    type="button"
                >
                    Explore More
                </button>

            </a>

        </div>

    </div>


    <!-- JEANS -->

    <div
        class="card mb-3 collection-card"
        style="background-image:url('JENS.jpg');"
    >

        <div class="card-header">

            NEW ARRIVAL

        </div>

        <div class="card-body">

            <h5>
                Jeans
            </h5>

            <a href="#JEANS">

                <button
                    class="btn explore-btn"
                    type="button"
                >
                    Explore More
                </button>

            </a>

        </div>

    </div>


    <!-- HOODIES -->

    <div
        class="card mb-3 collection-card"
        style="background-image:url('HODDIE.jpg');"
    >

        <div class="card-header">

            NEW ARRIVAL

        </div>

        <div class="card-body">

            <h5>
                Hoodies
            </h5>

            <a href="#HOODIES">

                <button
                    class="btn explore-btn"
                    type="button"
                >
                    Explore More
                </button>

            </a>

        </div>

    </div>


    <!-- TOPS -->

    <div
        class="card mb-3 collection-card"
        style="background-image:url('Elegant Wrap Midi Dress With Sheer Sleeves.jpg');"
    >

        <div class="card-header">

            NEW ARRIVAL

        </div>

        <div class="card-body">

            <h5>
                Tops
            </h5>

            <a href="#TOPS">

                <button
                    class="btn explore-btn"
                    type="button"
                >
                    Explore More
                </button>

            </a>

        </div>

    </div>


    <!-- 3-PIECE -->

    <div
        class="card mb-3 collection-card"
        style="background-image:url('Eid collection.jpg');"
    >

        <div class="card-header">

            NEW ARRIVAL

        </div>

        <div class="card-body">

            <h5>
                3-Piece Co-ord Set
            </h5>

            <a href="#THREE-PIECE">

                <button
                    class="btn explore-btn"
                    type="button"
                >
                    Explore More
                </button>

            </a>

        </div>

    </div>


    <!-- PARTY WEAR -->

    <div
        class="card mb-3 collection-card"
        style="background-image:url('Elegant Rose Pink Satin Dress.jpg');"
    >

        <div class="card-header">

            NEW ARRIVAL

        </div>

        <div class="card-body">

            <h5>
                Party Wear
            </h5>

            <a href="#PARTY-WEAR">

                <button
                    class="btn explore-btn"
                    type="button"
                >
                    Explore More
                </button>

            </a>

        </div>

    </div>

</div>


<!-- =========================================================
     T-SHIRTS COLLECTION
     ========================================================= -->

<h1
    id="T-SHIRT"
    class="collection-heading"
>

    T-SHIRTS COLLECTION

</h1>

<?php

show_cards(
    products_in(
        $products,
        "T-Shirts"
    )
);

?>


<!-- =========================================================
     JEANS COLLECTION
     ========================================================= -->

<h1
    id="JEANS"
    class="collection-heading"
>

    JEANS COLLECTION

</h1>

<?php

show_cards(
    products_in(
        $products,
        "Jeans"
    )
);

?>


<!-- =========================================================
     HOODIES COLLECTION
     ========================================================= -->

<h1
    id="HOODIES"
    class="collection-heading"
>

    HOODIES COLLECTION

</h1>

<?php

show_cards(
    products_in(
        $products,
        "Hoodies"
    )
);

?>


<!-- =========================================================
     TOPS COLLECTION
     ========================================================= -->

<h1
    id="TOPS"
    class="collection-heading"
>

    TOPS COLLECTION

</h1>

<?php

show_cards(
    products_in(
        $products,
        "Tops"
    )
);

?>


<!-- =========================================================
     PARTY WEAR COLLECTION
     ========================================================= -->

<h1
    id="PARTY-WEAR"
    class="collection-heading"
>

    PARTY WEAR COLLECTION

</h1>

<?php

show_cards(
    products_in(
        $products,
        "Party Wear"
    )
);

?>


<!-- =========================================================
     3-PIECE COLLECTION
     ========================================================= -->

<h1
    id="THREE-PIECE"
    class="collection-heading"
>

    3-PIECE COLLECTION

</h1>

<?php

show_cards(
    products_in(
        $products,
        "3-Piece"
    )
);

?>


<!-- =========================================================
     FOOTER
     ========================================================= -->

<footer class="FOOTER">


    <!-- ABOUT -->

    <div class="FOOTER-BOX">

        <h2>
            LARO
        </h2>

        <p>
            Discover timeless fashion,
            elegant designs, and styles
            made to express who you are.
        </p>

    </div>


    <!-- QUICK LINKS -->

    <div class="FOOTER-BOX">

        <h3>
            QUICK LINKS
        </h3>

        <a href="index.html">
            Home
        </a>

        <a href="newarrival.php">
            New Arrivals
        </a>

        <a href="dashboard.php">
            Dashboard
        </a>

    </div>


    <!-- CONTACT -->

    <div class="FOOTER-BOX">

        <h3>
            CONTACT US
        </h3>

        <p>
            Email: info@laro.com
        </p>

    </div>


</footer>


<!-- =========================================================
     COPYRIGHT
     ========================================================= -->

<div class="COPYRIGHT">

    <p>
        LARO Clothing Store
    </p>

</div>


<!-- =========================================================
     BOOTSTRAP JAVASCRIPT
     ========================================================= -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>

And your db.php must be this
<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "laro";   // CHANGE THIS TO YOUR DATABASE NAME

$conn = mysqli_connect(
    $host,
    $username,
    $password,
    $database
);

if (!$conn) {
    die(
        "Database connection failed: "
        . mysqli_connect_error()
    );
}

mysqli_set_charset(
    $conn,
    "utf8mb4"
);

?>