<?php
$hashedPassword = '$2y$13$SVu5xypIpWAHdY3kfWbNYuTrCAuqZC1vEem1TsNlrHqsriAqMk6h.';
$plainPassword = 'admin';

if (password_verify($plainPassword, $hashedPassword)) {
    echo "Valid\n";
} else {
    echo "Invalid\n";
}
