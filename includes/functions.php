<?php
/**
 * Copyright (c) 2026 Темирова Ангелина. All rights reserved.
 * Viewing and familiarization only. Any other use is prohibited.
 * Contact: temirovaangelina00@gmail.com
 */
function validateFormData($data) {
    return !empty($data['name']) && !empty($data['phone']) && !empty($data['email']) && !empty($data['message']);
}