package id.vibetool.app.ui.screens

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
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
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.unit.dp
import id.vibetool.app.data.ApiClient
import id.vibetool.app.data.RegisterRequest
import id.vibetool.app.ui.components.GlassCard
import id.vibetool.app.ui.components.GradientButton
import id.vibetool.app.ui.theme.Green
import id.vibetool.app.ui.theme.Red
import id.vibetool.app.ui.theme.TextMuted
import kotlinx.coroutines.launch
import org.json.JSONObject

@Composable
fun RegisterScreen(onBackToLogin: () -> Unit) {
    var name by remember { mutableStateOf("") }
    var email by remember { mutableStateOf("") }
    var whatsapp by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var passwordConfirm by remember { mutableStateOf("") }
    var loading by remember { mutableStateOf(false) }
    var error by remember { mutableStateOf<String?>(null) }
    var success by remember { mutableStateOf(false) }
    val scope = rememberCoroutineScope()

    fun doRegister() {
        if (name.isBlank() || email.isBlank() || password.isBlank()) {
            error = "Nama, email, dan password wajib diisi."
            return
        }
        if (password != passwordConfirm) {
            error = "Konfirmasi password tidak sama."
            return
        }
        loading = true
        error = null
        scope.launch {
            try {
                val res = ApiClient.api().register(
                    RegisterRequest(
                        name = name.trim(),
                        email = email.trim().lowercase(),
                        whatsappNumber = whatsapp.trim().ifBlank { null },
                        password = password,
                        passwordConfirmation = passwordConfirm,
                    )
                )
                if (res.isSuccessful) {
                    success = true
                } else {
                    val raw = res.errorBody()?.string()
                    error = try {
                        val obj = JSONObject(raw ?: "")
                        // Ambil pesan validasi pertama kalau ada
                        val errors = obj.optJSONObject("errors")
                        if (errors != null && errors.keys().hasNext()) {
                            val k = errors.keys().next()
                            errors.getJSONArray(k).getString(0)
                        } else obj.optString("message", "Pendaftaran gagal.")
                    } catch (e: Exception) {
                        "Pendaftaran gagal. Coba lagi."
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
        Spacer(Modifier.height(40.dp))
        Text("Daftar Member", style = MaterialTheme.typography.headlineMedium)
        Text(
            "Gratis — langsung jadi affiliator setelah aktif.",
            style = MaterialTheme.typography.bodyMedium,
            color = TextMuted,
        )
        Spacer(Modifier.height(28.dp))

        if (success) {
            GlassCard(modifier = Modifier.fillMaxWidth()) {
                Column {
                    Text("Pendaftaran berhasil! 🎉", style = MaterialTheme.typography.titleMedium, color = Green)
                    Spacer(Modifier.height(8.dp))
                    Text(
                        "Akunmu berstatus menunggu aktivasi. Hubungi admin via WhatsApp untuk mengaktifkan akun, lalu login di sini.",
                        style = MaterialTheme.typography.bodyMedium,
                        color = TextMuted,
                    )
                }
            }
            Spacer(Modifier.height(20.dp))
            GradientButton("Kembali ke Login", onClick = onBackToLogin, modifier = Modifier.fillMaxWidth())
        } else {
            OutlinedTextField(
                value = name, onValueChange = { name = it },
                label = { Text("Nama Lengkap") }, singleLine = true,
                modifier = Modifier.fillMaxWidth(),
            )
            Spacer(Modifier.height(12.dp))
            OutlinedTextField(
                value = email, onValueChange = { email = it },
                label = { Text("Email") }, singleLine = true,
                keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Email),
                modifier = Modifier.fillMaxWidth(),
            )
            Spacer(Modifier.height(12.dp))
            OutlinedTextField(
                value = whatsapp, onValueChange = { whatsapp = it },
                label = { Text("No. WhatsApp (opsional)") }, singleLine = true,
                keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Phone),
                modifier = Modifier.fillMaxWidth(),
            )
            Spacer(Modifier.height(12.dp))
            OutlinedTextField(
                value = password, onValueChange = { password = it },
                label = { Text("Password") }, singleLine = true,
                visualTransformation = PasswordVisualTransformation(),
                keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Password),
                modifier = Modifier.fillMaxWidth(),
            )
            Spacer(Modifier.height(12.dp))
            OutlinedTextField(
                value = passwordConfirm, onValueChange = { passwordConfirm = it },
                label = { Text("Ulangi Password") }, singleLine = true,
                visualTransformation = PasswordVisualTransformation(),
                keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Password),
                modifier = Modifier.fillMaxWidth(),
            )

            error?.let {
                Spacer(Modifier.height(12.dp))
                Text(it, color = Red, style = MaterialTheme.typography.bodyMedium)
            }

            Spacer(Modifier.height(24.dp))
            GradientButton("Daftar", onClick = { doRegister() }, loading = loading, modifier = Modifier.fillMaxWidth())
            Spacer(Modifier.height(12.dp))
            TextButton(onClick = onBackToLogin) {
                Text("Sudah punya akun? Masuk", color = MaterialTheme.colorScheme.tertiary)
            }
        }
        Spacer(Modifier.height(40.dp))
    }
}
