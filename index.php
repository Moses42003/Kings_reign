<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <!-- link css file -->
    <link rel="stylesheet" href="styles/home.css">

    <!-- icon -->
    <link rel="shortcut icon" href="images/logos/logo-black.jpg" type="image/x-icon">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(rgba(26,35,126,0.7), rgba(26,35,126,0.7)), url('images/bg-shop.jpg') center/cover no-repeat fixed;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .des {
            background: rgba(255,255,255,0.92);
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(26,35,126,0.13);
            padding: 40px 28px 32px 28px;
            margin-top: 60px;
            max-width: 420px;
            text-align: center;
        }
        .des h2 {
            color: #1a237e;
            font-size: 2.2rem;
            margin-bottom: 12px;
        }
        .des p {
            color: #333;
            font-size: 1.1rem;
            margin-bottom: 18px;
        }
        .des .btn {
            margin: 18px 0 10px 0;
        }
        .des .btn a button {
            background: #1a237e;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 12px 38px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }
        .des .btn a button:hover {
            background: #3949ab;
        }
        .site-icons {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 22px 0 10px 0;
        }
        .site-icons img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(26,35,126,0.08);
        }
        @media (max-width: 600px) {
            .des {
                padding: 22px 8px 18px 8px;
                max-width: 98vw;
            }
            .site-icons img {
                width: 44px;
                height: 44px;
            }
        }
    </style>
</head>
<body>
    

    <!-- short description and register -->
    <div class="des">
        <h2>Welcome to <strong>Kings Reign</strong></h2>
        <div class="site-icons">
            <img src="images/logos/logo-blue.jpg" alt="Phones">
            <img src="images/logos/logo-white.jpg" alt="Clothings">
        </div>
        <p>Your one-stop shop for the latest smartphones, trendy clothing, and unbeatable deals!</p>
        <p>Discover a wide range of top-brand phones, stylish outfits, and accessories all in one place. Shop with confidence and enjoy fast delivery, secure payment, and excellent customer support.</p>
        <p>Ready to upgrade your style or get a new device? Join our community of happy customers today!</p>
        <div class="btn">
            <a href="login.php"><button>Register / Login</button></a>
        </div>
        <p style="color:#1a237e; font-size:1rem; margin-top:10px;">Click the button above to get started.</p>
    </div>
</body>
</html>