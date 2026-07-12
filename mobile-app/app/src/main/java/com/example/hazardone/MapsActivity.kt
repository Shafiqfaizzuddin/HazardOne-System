package com.example.hazardone

import android.Manifest
import android.annotation.SuppressLint
import android.content.Intent
import android.content.pm.PackageManager
import android.graphics.Bitmap
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.PorterDuff
import android.os.Bundle
import android.widget.Toast
import androidx.activity.enableEdgeToEdge
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import androidx.core.view.updatePadding
import com.example.hazardone.databinding.ActivityMapsBinding
import com.example.hazardone.network.Hazard
import com.example.hazardone.network.RetrofitClient
import com.google.android.gms.location.FusedLocationProviderClient
import com.google.android.gms.location.LocationServices
import com.google.android.gms.location.Priority
import com.google.android.gms.maps.CameraUpdateFactory
import com.google.android.gms.maps.GoogleMap
import com.google.android.gms.maps.OnMapReadyCallback
import com.google.android.gms.maps.SupportMapFragment
import com.google.android.gms.maps.model.BitmapDescriptor
import com.google.android.gms.maps.model.BitmapDescriptorFactory
import com.google.android.gms.maps.model.LatLng
import com.google.android.gms.maps.model.MarkerOptions
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class MapsActivity : AppCompatActivity(), OnMapReadyCallback {

    private lateinit var binding: ActivityMapsBinding
    private lateinit var googleMap: GoogleMap
    private lateinit var fusedLocationClient: FusedLocationProviderClient

    private val locationPermissionLauncher =
        registerForActivityResult(
            ActivityResultContracts.RequestMultiplePermissions()
        ) { permissions ->
            val fineGranted =
                permissions[Manifest.permission.ACCESS_FINE_LOCATION] == true
            val coarseGranted =
                permissions[Manifest.permission.ACCESS_COARSE_LOCATION] == true

            if (fineGranted || coarseGranted) {
                enableCurrentLocation()
            } else {
                Toast.makeText(
                    this,
                    "Location permission is required to show your position.",
                    Toast.LENGTH_LONG
                ).show()
            }
        }

    private val reportLauncher =
        registerForActivityResult(
            ActivityResultContracts.StartActivityForResult()
        ) { result ->
            if (result.resultCode == RESULT_OK) {
                loadHazardMarkers()
            }
        }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()

        fusedLocationClient =
            LocationServices.getFusedLocationProviderClient(this)

        binding = ActivityMapsBinding.inflate(layoutInflater)
        setContentView(binding.root)

        setSupportActionBar(binding.toolbar)

        ViewCompat.setOnApplyWindowInsetsListener(binding.root) { _, insets ->
            val systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars())
            binding.reportButton.updatePadding(
                left = 0,
                top = 0,
                right = 0,
                bottom = systemBars.bottom
            )
            binding.myLocationFab.updatePadding(
                left = 0,
                top = 0,
                right = 0,
                bottom = systemBars.bottom
            )
            insets
        }

        val mapFragment = supportFragmentManager
            .findFragmentById(R.id.map) as SupportMapFragment
        mapFragment.getMapAsync(this)

        binding.reportButton.setOnClickListener {
            reportAtCurrentLocation()
        }

        binding.myLocationFab.setOnClickListener {
            animateToCurrentLocation()
        }
    }

    override fun onCreateOptionsMenu(menu: android.view.Menu?): Boolean {
        menuInflater.inflate(R.menu.main_menu, menu)
        return true
    }

    override fun onOptionsItemSelected(item: android.view.MenuItem): Boolean {
        return when (item.itemId) {
            R.id.action_about -> {
                startActivity(Intent(this, AboutActivity::class.java))
                true
            }
            else -> super.onOptionsItemSelected(item)
        }
    }

    override fun onMapReady(map: GoogleMap) {
        googleMap = map

        requestLocationPermission()
        loadHazardMarkers()

        googleMap.setOnMapLongClickListener { selectedLocation ->
            openReportScreen(selectedLocation)
        }
    }

    private fun requestLocationPermission() {
        val fineGranted = ContextCompat.checkSelfPermission(
            this,
            Manifest.permission.ACCESS_FINE_LOCATION
        ) == PackageManager.PERMISSION_GRANTED

        val coarseGranted = ContextCompat.checkSelfPermission(
            this,
            Manifest.permission.ACCESS_COARSE_LOCATION
        ) == PackageManager.PERMISSION_GRANTED

        if (fineGranted || coarseGranted) {
            enableCurrentLocation()
        } else {
            locationPermissionLauncher.launch(
                arrayOf(
                    Manifest.permission.ACCESS_FINE_LOCATION,
                    Manifest.permission.ACCESS_COARSE_LOCATION
                )
            )
        }
    }

    @SuppressLint("MissingPermission")
    private fun enableCurrentLocation() {
        googleMap.isMyLocationEnabled = true
        googleMap.uiSettings.isMyLocationButtonEnabled = false
        binding.myLocationFab.visibility = android.view.View.VISIBLE

        animateToCurrentLocation()
    }

    @SuppressLint("MissingPermission")
    private fun animateToCurrentLocation() {
        fusedLocationClient
            .getCurrentLocation(
                Priority.PRIORITY_HIGH_ACCURACY,
                null
            )
            .addOnSuccessListener { location ->
                if (location != null) {
                    val currentPosition = LatLng(
                        location.latitude,
                        location.longitude
                    )

                    googleMap.animateCamera(
                        CameraUpdateFactory.newLatLngZoom(
                            currentPosition,
                            16f
                        )
                    )
                }
            }
    }

    @SuppressLint("MissingPermission")
    private fun reportAtCurrentLocation() {
        fusedLocationClient.lastLocation.addOnSuccessListener { location ->
            if (location != null) {
                openReportScreen(LatLng(location.latitude, location.longitude))
            } else {
                Toast.makeText(this, "Fetching current location. Please try again in a moment.", Toast.LENGTH_SHORT).show()
                // Trigger a fresh location update
                fusedLocationClient.getCurrentLocation(Priority.PRIORITY_HIGH_ACCURACY, null)
            }
        }
    }

    private fun loadHazardMarkers() {
        RetrofitClient.apiService
            .getHazards()
            .enqueue(object : Callback<List<Hazard>> {

                override fun onResponse(
                    call: Call<List<Hazard>>,
                    response: Response<List<Hazard>>
                ) {
                    if (!response.isSuccessful) {
                        Toast.makeText(
                            this@MapsActivity,
                            "Unable to download hazard reports.",
                            Toast.LENGTH_SHORT
                        ).show()

                        return
                    }

                    googleMap.clear()
                    response.body()
                        .orEmpty()
                        .forEach { hazard ->
                            addHazardMarker(hazard)
                        }
                }

                override fun onFailure(
                    call: Call<List<Hazard>>,
                    throwable: Throwable
                ) {
                    Toast.makeText(
                        this@MapsActivity,
                        "Server error: ${throwable.message}",
                        Toast.LENGTH_LONG
                    ).show()
                }
            })
    }

    private fun addHazardMarker(hazard: Hazard) {
        val (iconRes, color) = when (hazard.category) {
            "Road" -> Pair(R.drawable.ic_hazard_road, Color.RED)
            "Environmental" -> Pair(R.drawable.ic_hazard_environmental, Color.GREEN)
            "Building" -> Pair(R.drawable.ic_hazard_building, Color.parseColor("#FFA500")) // Orange
            else -> Pair(R.drawable.ic_hazard_road, Color.MAGENTA)
        }

        googleMap.addMarker(
            MarkerOptions()
                .position(
                    LatLng(
                        hazard.latitude,
                        hazard.longitude
                    )
                )
                .title("${hazard.category} Hazard")
                .snippet(
                    "${hazard.description}\n" +
                            "${hazard.locationName}\n" +
                            "Reported by ${hazard.userName}\n" +
                            hazard.reportedAt
                )
                .icon(getBitmapDescriptor(iconRes, color))
        )
    }

    private fun getBitmapDescriptor(resId: Int, color: Int): BitmapDescriptor? {
        val size = 100
        val bitmap = Bitmap.createBitmap(size, size + 25, Bitmap.Config.ARGB_8888)
        val canvas = Canvas(bitmap)
        val paint = android.graphics.Paint(android.graphics.Paint.ANTI_ALIAS_FLAG)

        // Draw pin tail (white triangle at the bottom)
        val path = android.graphics.Path()
        path.moveTo(size * 0.35f, size * 0.8f)
        path.lineTo(size * 0.5f, size + 25f)
        path.lineTo(size * 0.65f, size * 0.8f)
        path.close()
        paint.color = Color.WHITE
        canvas.drawPath(path, paint)

        // Draw the white border circle (head)
        paint.color = Color.WHITE
        canvas.drawCircle(size / 2f, size / 2f, size / 2f, paint)

        // Draw the colored inner circle
        paint.color = color
        canvas.drawCircle(size / 2f, size / 2f, (size / 2f) * 0.85f, paint)

        // Draw the icon inside the circle
        val drawable = ContextCompat.getDrawable(this, resId) ?: return null
        drawable.mutate()
        drawable.setColorFilter(Color.WHITE, PorterDuff.Mode.SRC_IN)
        val iconPadding = (size * 0.25f).toInt()
        drawable.setBounds(iconPadding, iconPadding, size - iconPadding, size - iconPadding)
        drawable.draw(canvas)

        return BitmapDescriptorFactory.fromBitmap(bitmap)
    }

    private fun openReportScreen(position: LatLng) {
        val intent = Intent(
            this,
            ReportHazardActivity::class.java
        ).apply {
            putExtra("latitude", position.latitude)
            putExtra("longitude", position.longitude)
        }

        reportLauncher.launch(intent)
    }
}