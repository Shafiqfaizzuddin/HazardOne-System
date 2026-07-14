package com.example.hazardone.network

import retrofit2.Call
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST

interface ApiService {

    @GET("get_hazards.php")
    fun getHazards(): Call<List<Hazard>>

    @POST("add_hazard.php")
    fun addHazard(
        @Body request: HazardRequest
    ): Call<ApiResponse>
}