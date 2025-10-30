<!doctype html>
<html lang="de" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Geex Dashboard - PHP Template</title>

    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/css/bootstrap/bootstrap.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.27.0/dist/apexcharts.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/custom-layout-fix.css">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon.svg">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@iconscout/unicons@4.0.8/css/line.min.css">
    
    <script>
        if (localStorage.theme) document.documentElement.setAttribute("data-theme", localStorage.theme);
        if (localStorage.layout) document.documentElement.setAttribute("data-nav", localStorage.navbar);
        if (localStorage.layout) document.documentElement.setAttribute("dir", localStorage.layout);
    </script>
    
    <style>
        /* Loading Indicator */
        .page-loader {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
        }
        .page-loader.active {
            display: block;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #AB54DB;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>

<body class="geex-dashboard">
    
    <!-- Loading Indicator -->
    <div class="page-loader" id="pageLoader">
        <div class="spinner"></div>
    </div>

    <header class="geex-header">
        <div class="geex-header__wrapper">
            <div class="geex-header__logo-wrapper">
                <a href="#" data-page="dashboard" class="geex-header__logo page-link">
                    <img class="logo-lite" src="assets/logo-dark.svg" alt="Header logo" />
                    <img class="logo-dark" src="assets/logo-lite.svg" alt="Header logo" />
                </a>
            </div>
            <nav class="geex-header__menu-wrapper">
                <ul class="geex-header__menu">
                    <li class="geex-header__menu__item has-children">
                        <a href="#" class="geex-header__menu__link">
                            <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_1139_9707)">
                                <path d="M21.1943 8.31319L14.2413 1.35936C13.3808 0.501345 12.2152 0.0195312 11 0.0195312C9.78482 0.0195312 8.61921 0.501345 7.75868 1.35936L0.805761 8.31319C0.549484 8.56782 0.3463 8.8708 0.207987 9.20454C0.0696733 9.53829 -0.00101787 9.89617 1.10729e-05 10.2574V19.2564C1.10729e-05 19.9857 0.289742 20.6852 0.805467 21.2009C1.32119 21.7166 2.02067 22.0064 2.75001 22.0064H19.25C19.9794 22.0064 20.6788 21.7166 21.1946 21.2009C21.7103 20.6852 22 19.9857 22 19.2564V10.2574C22.001 9.89617 21.9303 9.53829 21.792 9.20454C21.6537 8.8708 21.4505 8.56782 21.1943 8.31319ZM13.75 20.173H8.25001V16.5669C8.25001 15.8375 8.53974 15.138 9.05547 14.6223C9.57119 14.1066 10.2707 13.8169 11 13.8169C11.7294 13.8169 12.4288 14.1066 12.9446 14.6223C13.4603 15.138 13.75 15.8375 13.75 16.5669V20.173ZM20.1667 19.2564C20.1667 19.4995 20.0701 19.7326 19.8982 19.9045C19.7263 20.0764 19.4931 20.173 19.25 20.173H15.5833V16.5669C15.5833 15.3513 15.1005 14.1855 14.2409 13.3259C13.3814 12.4664 12.2156 11.9835 11 11.9835C9.78444 11.9835 8.61865 12.4664 7.75911 13.3259C6.89956 14.1855 6.41668 15.3513 6.41668 16.5669V20.173H2.75001C2.5069 20.173 2.27374 20.0764 2.10183 19.9045C1.92992 19.7326 1.83334 19.4995 1.83334 19.2564V10.2574C1.83419 10.0145 1.93068 9.78168 2.10193 9.60935L9.05485 2.65827C9.57157 2.14396 10.271 1.85522 11 1.85522C11.7291 1.85522 12.4285 2.14396 12.9452 2.65827L19.8981 9.61211C20.0687 9.78375 20.1651 10.0155 20.1667 10.2574V19.2564Z" fill="#B9BBBD"/>
                                </g>
                            </svg>                                                  
                            <span>Demo</span>
                        </a>  
                        <ul class="geex-header__submenu">
                            <li class="geex-header__menu__item">
                                <a href="#" data-page="dashboard" class="geex-header__menu__link page-link">Dashboard</a>
                            </li>
                            <li class="geex-header__menu__item">
                                <a href="#" data-page="server" class="geex-header__menu__link page-link">Server Management</a>
                            </li>
                            <li class="geex-header__menu__item">
                                <a href="#" data-page="banking" class="geex-header__menu__link page-link">Banking</a>
                            </li>
                        </ul>
                    </li>

                    <li class="geex-header__menu__item has-children">
                        <a href="#" class="geex-header__menu__link">
                            <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6.41667 0H3.66667C2.69421 0 1.76158 0.386308 1.07394 1.07394C0.386308 1.76158 0 2.69421 0 3.66667L0 6.41667C0 7.38913 0.386308 8.32176 1.07394 9.00939C1.76158 9.69702 2.69421 10.0833 3.66667 10.0833H6.41667C7.38913 10.0833 8.32176 9.69702 9.00939 9.00939C9.69702 8.32176 10.0833 7.38913 10.0833 6.41667V3.66667C10.0833 2.69421 9.69702 1.76158 9.00939 1.07394C8.32176 0.386308 7.38913 0 6.41667 0V0ZM8.25 6.41667C8.25 6.9029 8.05684 7.36921 7.71303 7.71303C7.36921 8.05684 6.9029 8.25 6.41667 8.25H3.66667C3.18044 8.25 2.71412 8.05684 2.3703 7.71303C2.02649 7.36921 1.83333 6.9029 1.83333 6.41667V3.66667C1.83333 3.18044 2.02649 2.71412 2.3703 2.3703C2.71412 2.02649 3.18044 1.83333 3.66667 1.83333H6.41667C6.9029 1.83333 7.36921 2.02649 7.71303 2.3703C8.05684 2.71412 8.25 3.18044 8.25 3.66667V6.41667Z" fill="#B9BBBD"/>
                            </svg>                                                  
                            <span>App</span>
                        </a>  
                        <ul class="geex-header__submenu">
                            <li class="geex-header__menu__item">
                                <a href="#" data-page="todo" class="geex-header__menu__link page-link">Todo</a>
                            </li>
                            <li class="geex-header__menu__item">
                                <a href="#" data-page="chat" class="geex-header__menu__link page-link">Chat</a>
                            </li>
                            <li class="geex-header__menu__item">
                                <a href="#" data-page="blog" class="geex-header__menu__link page-link">Blog</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>
            <div class="geex-header__action">
                <div class="geex-header__action__item">
                    <button class="geex-btn geex-btn__customizer">
                        <i class="uil uil-pen"></i> 
                        <span>Customizer</span>
                    </button>
                </div> 
            </div>
        </div>
    </header>