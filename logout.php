<?php
session_start();
session_destroy();
header('Location: /kaia_sv/index.php');