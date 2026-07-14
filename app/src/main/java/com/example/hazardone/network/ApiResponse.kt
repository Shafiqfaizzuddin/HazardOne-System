package com.example.hazardone.network

data class ApiResponse(
    val success: Boolean,
    val message: String,
    val id: Int?
)