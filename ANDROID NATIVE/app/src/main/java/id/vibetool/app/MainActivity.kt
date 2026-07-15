package id.vibetool.app

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import id.vibetool.app.data.ApiClient
import id.vibetool.app.ui.AppNav
import id.vibetool.app.ui.theme.VibeToolTheme

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        ApiClient.init(applicationContext)
        setContent {
            VibeToolTheme {
                AppNav()
            }
        }
    }
}
