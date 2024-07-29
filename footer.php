                <?php
                if (is_admin_login()) {
                ?>
                    </main>
                    <footer class="py-4 bg-light mt-auto">
                        <div class="container-fluid px-4">
                            <div class="d-flex align-items-center justify-content-end small">
                                <div class="text-muted">Copyright &copy; Library Management System <?php echo date('Y'); ?></div>
                            </div>
                        </div>
                    </footer>
                    </div>
                    </div>
                <?php
                } else {
                ?>
                    <footer class="pt-3 mt-4 text-muted text-center border-top">
                        &copy; <?php echo date('Y'); ?>
                    </footer>
                    </div>
                    </main>
                <?php
                }
                ?>

                
                </body>

                </html>