package id.vibetool.app.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Bolt
import androidx.compose.material.icons.filled.Visibility
import androidx.compose.material.icons.filled.VisibilityOff
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.input.VisualTransformation
import androidx.compose.ui.unit.dp
import id.vibetool.app.data.ApiClient
import id.vibetool.app.data.LoginRequest
import id.vibetool.app.ui.components.GradientButton
import id.vibetool.app.ui.theme.GradientPrimary
import id.vibetool.app.ui.theme.Red
import id.vibetool.app.ui.theme.TextMuted
import kotlinx.coroutines.launch
import org.json.JSONObject

@Composable
fun LoginScreen(
    onLoginSuccess: () -> Unit,
    onGoRegister: () -> Unit,
) {
    var email by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var showPassword by remember { mutableStateOf(false) }
    var loading by remember { mutableStateOf(false) }
    var error by remember { mutableStateOf<String?>(null) }
    val scope = rememberCoroutineScope()

    fun doLogin() {
        if (email.isBlank() || password.isBlank()) {
            error = "Email dan password wajib diisi."
            return
        }
        loading = true
        error = null
        scope.launch {
            try {
                val res = ApiClient.api().login(LoginRequest(email.trim(), password))
                val body = res.body()
                if (res.isSuccessful && body?.ok == true && body.token != null) {
                    ApiClient.tokens().token = body.token
                    ApiClient.tokens().userName = body.user?.name
                    onLoginSuccess()
                } else {
                    // Ambil pesan error dari body JSON (401/403)
                    error = body?.message ?: run {
                        val raw = res.errorBody()?.string()
                        try { JSONObject(raw ?: "").optString("message", "Login gagal.") }
                        catch (e: Exception) { "Login gagal. Coba lagi." }
                    }
                }
            } catch (e: Exception) {
                error = "Tidak bisa terhubung ke server. Periksa koneksi internetmu."
            }
            loading = false
        }
    }

    Column(
        modifier = Modifier
            .fillMaxSize()
            .verticalScroll(rememberScrollState())
            .padding(horizontal = 28.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center,
    ) {
        Spacer(Modifier.height(48.dp))

        // Logo mark
        Box(
            modifier = Modifier
                .size(84.dp)
                .background(GradientPrimary, CircleShape),
            contentAlignment = Alignment.Center,
        ) {
            Icon(
                Icons.Filled.Bolt,
                contentDescription = null,
                tint = Color.White,
                modifier = Modifier.size(44.dp),
            )
        }
        Spacer(Modifier.height(20.dp))
        Text("VibeTool.Id", style = MaterialTheme.typography.headlineLarge)
        Text(
            "Marketplace Produk Digital",
            style = MaterialTheme.typography.bodyMedium,
            color = TextMuted,
        )

        Spacer(Modifier.height(36.dp))

        OutlinedTextField(
            value = email,
            onValueChange = { email = it },
            label = { Text("Email") },
            singleLine = true,
            keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Email),
            modifier = Modifier.fillMaxWidth(),
        )
        Spacer(Modifier.height(14.dp))
        OutlinedTextField(
            value = password,
            onValueChange = { password = it },
            label = { Text("Password") },
            singleLine = true,
            visualTransformation = if (showPassword) VisualTransformation.None else PasswordVisualTransformation(),
            keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Password),
            trailingIcon = {
                IconButton(onClick = { showPassword = !showPassword }) {
                    Icon(
                        if (showPassword) Icons.Filled.VisibilityOff else Icons.Filled.Visibility,
                        contentDescription = "Tampilkan password",
                        tint = TextMuted,
                    )
                }
            },
            modifier = Modifier.fillMaxWidth(),
        )

        error?.let {
            Spacer(Modifier.height(12.dp))
            Text(it, color = Red, style = MaterialTheme.typography.bodyMedium)
        }

        Spacer(Modifier.height(24.dp))
        GradientButton(
            text = "Masuk",
            onClick = { doLogin() },
            loading = loading,
            modifier = Modifier.fillMaxWidth(),
        )

        Spacer(Modifier.height(16.dp))
        TextButton(onClick = onGoRegister) {
            Text("Belum punya akun? Daftar sekarang", color = MaterialTheme.colorScheme.tertiary)
        }
        Spacer(Modifier.height(48.dp))
    }
}
