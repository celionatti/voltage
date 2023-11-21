<?php

/**
 * Package Name: Voltage
 * Author: Celio natti
 * version: 1.0.0
 * Year: 2023
 * 
 */

?>


<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <a class="nav-link" href="<?= URL_ROOT . 'admin' ?>">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>
                <div class="sb-sidenav-menu-heading">Pages</div>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseUsers" aria-expanded="false" aria-controls="collapseUsers">
                    <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                    Users
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseUsers" aria-labelledby="users" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="<?= URL_ROOT . 'admin/manage-users' ?>">Manage Users</a>
                        <a class="nav-link" href="<?= URL_ROOT . 'admin/users' ?>">Create Users</a>
                        <a class="nav-link" href="<?= URL_ROOT . 'admin/blocked-users' ?>">Blocked Users</a>
                    </nav>
                </div>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseQuiz" aria-expanded="false" aria-controls="collapseQuiz">
                    <div class="sb-nav-link-icon"><i class="fas fa-circle-question"></i></div>
                    Quiz | Tasks
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseQuiz" aria-labelledby="quiz" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="<?= URL_ROOT . 'admin/tasks' ?>">Tasks</a>
                        <a class="nav-link" href="<?= URL_ROOT . 'admin/quiz' ?>">Quiz</a>
                    </nav>
                </div>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseBlog" aria-expanded="false" aria-controls="collapseBlog">
                    <div class="sb-nav-link-icon"><i class="fas fa-newspaper"></i></div>
                    Blog
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseBlog" aria-labelledby="blog" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="<?= URL_ROOT . 'admin/blog' ?>">Create Blog</a>
                        <a class="nav-link" href="<?= URL_ROOT . 'admin/manage-blog' ?>">Manage Blog</a>
                    </nav>
                </div>
                
                <a class="nav-link" href="<?= URL_ROOT . 'admin/credits-wallets' ?>">
                    <div class="sb-nav-link-icon"><i class="fas fa-wallet"></i></div>
                    Credit & Wallets
                </a>

                <a class="nav-link" href="<?= URL_ROOT ?>">
                    <div class="sb-nav-link-icon"><i class="fas fa-globe"></i></div>
                    Visit Site
                </a>
            </div>
        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Logged in as:</div>
            <span class="text-capitalize">celio natti</span>
        </div>
    </nav>
</div>