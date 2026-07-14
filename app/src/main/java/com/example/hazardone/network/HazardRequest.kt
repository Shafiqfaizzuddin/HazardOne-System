package com.example.hazardone.network

import com.google.gson.annotations.SerializedName

data class HazardRequest(
    @SerializedName("user_name")
    val userName: String,

    @SerializedName("user_agent")
    val userAgent: String,

    @SerializedName("location_name")
    val locationName: String,

    val latitude: Double,
    val longitude: Double,
    val category: String,
    val description: String
)