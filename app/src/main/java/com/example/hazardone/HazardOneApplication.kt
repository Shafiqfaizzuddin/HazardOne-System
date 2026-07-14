package com.example.hazardone

import android.app.Application

class HazardOneApplication : Application() {
    override fun onCreate() {
        super.onCreate()
        ThemePreference.applySavedTheme(this)
    }
}
