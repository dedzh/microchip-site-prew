<?php
function validateFormData($data) {
    return !empty($data['name']) && !empty($data['phone']) && !empty($data['email']) && !empty($data['message']);
}