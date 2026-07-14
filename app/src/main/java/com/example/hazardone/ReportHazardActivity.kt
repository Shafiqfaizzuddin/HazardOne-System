package com.example.hazardone

import android.os.Build
import android.os.Bundle
import android.widget.ArrayAdapter
import android.widget.Toast
import androidx.activity.enableEdgeToEdge
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import androidx.core.view.updatePadding
import com.example.hazardone.databinding.ActivityReportHazardBinding
import com.example.hazardone.network.ApiResponse
import com.example.hazardone.network.HazardRequest
import com.example.hazardone.network.RetrofitClient
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class ReportHazardActivity : AppCompatActivity() {

    private lateinit var binding: ActivityReportHazardBinding

    private val latitude by lazy {
        intent.getDoubleExtra("latitude", 0.0)
    }

    private val longitude by lazy {
        intent.getDoubleExtra("longitude", 0.0)
    }

    private val categories = listOf(
        "Road",
        "Environmental",
        "Building"
    )

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        binding = ActivityReportHazardBinding.inflate(layoutInflater)
        setContentView(binding.root)

        ViewCompat.setOnApplyWindowInsetsListener(binding.main) { _, insets ->
            val systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars())
            binding.reportScroll.updatePadding(
                left = systemBars.left,
                right = systemBars.right,
                bottom = systemBars.bottom
            )
            insets
        }

        setupUI()
    }

    private fun setupUI() {
        binding.latitudeText.text = getString(R.string.label_latitude, latitude.toString())
        binding.longitudeText.text = getString(R.string.label_longitude, longitude.toString())

        val adapter = ArrayAdapter(this, android.R.layout.simple_spinner_item, categories)
        adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
        binding.categorySpinner.adapter = adapter

        binding.submitButton.setOnClickListener {
            val userName = binding.userNameInput.text.toString()
            val locationName = binding.locationNameInput.text.toString()
            val category = binding.categorySpinner.selectedItem.toString()
            val description = binding.descriptionInput.text.toString()

            if (userName.isBlank()) {
                binding.userNameInput.error = getString(R.string.error_name_empty)
                return@setOnClickListener
            }

            if (locationName.isBlank()) {
                binding.locationNameInput.error = getString(R.string.error_location_empty)
                return@setOnClickListener
            }

            if (description.isBlank()) {
                binding.descriptionInput.error = getString(R.string.error_description_empty)
                return@setOnClickListener
            }

            submitHazard(userName, locationName, category, description)
        }
    }

    private fun getDeviceInformation(): String {
        return "${Build.MANUFACTURER} ${Build.MODEL}; " +
                "Android ${Build.VERSION.RELEASE}; " +
                "SDK ${Build.VERSION.SDK_INT}"
    }

    private fun submitHazard(
        userName: String,
        locationName: String,
        category: String,
        description: String
    ) {
        val request = HazardRequest(
            userName = userName.trim(),
            userAgent = getDeviceInformation(),
            locationName = locationName.trim(),
            latitude = latitude,
            longitude = longitude,
            category = category,
            description = description.trim()
        )

        RetrofitClient.apiService
            .addHazard(request)
            .enqueue(object : Callback<ApiResponse> {

                override fun onResponse(
                    call: Call<ApiResponse>,
                    response: Response<ApiResponse>
                ) {
                    val result = response.body()

                    if (response.isSuccessful &&
                        result?.success == true
                    ) {
                        Toast.makeText(
                            this@ReportHazardActivity,
                            result.message,
                            Toast.LENGTH_LONG
                        ).show()

                        setResult(RESULT_OK)
                        finish()
                    } else {
                        Toast.makeText(
                            this@ReportHazardActivity,
                            result?.message
                                ?: getString(R.string.error_generic),
                            Toast.LENGTH_LONG
                        ).show()
                    }
                }

                override fun onFailure(
                    call: Call<ApiResponse>,
                    throwable: Throwable
                ) {
                    Toast.makeText(
                        this@ReportHazardActivity,
                        getString(R.string.error_server, throwable.message),
                        Toast.LENGTH_LONG
                    ).show()
                }
            })
    }
}
