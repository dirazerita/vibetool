package id.vibetool.app.ui

import androidx.compose.foundation.clickable
import androidx.compose.foundation.interaction.MutableInteractionSource
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Apps
import androidx.compose.material.icons.filled.Home
import androidx.compose.material.icons.filled.Key
import androidx.compose.material.icons.filled.Paid
import androidx.compose.material.icons.filled.Person
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.remember
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.navigation.NavGraph.Companion.findStartDestination
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.currentBackStackEntryAsState
import androidx.navigation.compose.rememberNavController
import id.vibetool.app.data.ApiClient
import id.vibetool.app.ui.screens.CommissionsScreen
import id.vibetool.app.ui.screens.CouponsScreen
import id.vibetool.app.ui.screens.HomeScreen
import id.vibetool.app.ui.screens.LicensesScreen
import id.vibetool.app.ui.screens.LoginScreen
import id.vibetool.app.ui.screens.MenuScreen
import id.vibetool.app.ui.screens.ProductDetailScreen
import id.vibetool.app.ui.screens.ProfileScreen
import id.vibetool.app.ui.screens.PurchasesScreen
import id.vibetool.app.ui.screens.RegisterScreen
import id.vibetool.app.ui.screens.TeamPurchasesScreen
import id.vibetool.app.ui.screens.TeamScreen
import id.vibetool.app.ui.screens.WithdrawalsScreen
import id.vibetool.app.ui.components.skeuoInset
import id.vibetool.app.ui.components.skeuoRaised
import id.vibetool.app.ui.theme.BgDeep
import id.vibetool.app.ui.theme.IndigoLight
import id.vibetool.app.ui.theme.TextMuted

private data class BottomTab(
    val route: String,
    val label: String,
    val icon: ImageVector,
)

private val bottomTabs = listOf(
    BottomTab("home", "Beranda", Icons.Filled.Home),
    BottomTab("commissions", "Komisi", Icons.Filled.Paid),
    BottomTab("licenses", "Lisensi", Icons.Filled.Key),
    BottomTab("menu", "Menu", Icons.Filled.Apps),
    BottomTab("profile", "Profil", Icons.Filled.Person),
)

@Composable
fun AppNav() {
    val navController = rememberNavController()
    val backStack by navController.currentBackStackEntryAsState()
    val currentRoute = backStack?.destination?.route

    val isLoggedIn = ApiClient.tokens().isLoggedIn()
    val showBottomBar = currentRoute in bottomTabs.map { it.route }

    Scaffold(
        containerColor = BgDeep,
        bottomBar = {
            if (showBottomBar) {
                SkeuoBottomBar(
                    currentRoute = currentRoute,
                    onTabClick = { route ->
                        navController.navigate(route) {
                            popUpTo(navController.graph.findStartDestination().id) {
                                saveState = true
                            }
                            launchSingleTop = true
                            restoreState = true
                        }
                    },
                )
            }
        },
    ) { padding ->
        NavHost(
            navController = navController,
            startDestination = if (isLoggedIn) "home" else "login",
            modifier = Modifier.padding(padding),
        ) {
            composable("login") {
                LoginScreen(
                    onLoginSuccess = {
                        navController.navigate("home") {
                            popUpTo("login") { inclusive = true }
                        }
                    },
                    onGoRegister = { navController.navigate("register") },
                )
            }
            composable("register") {
                RegisterScreen(onBackToLogin = { navController.popBackStack() })
            }
            composable("home") {
                HomeScreen(
                    onOpenProduct = { slug -> navController.navigate("product/$slug") },
                    onOpenTeam = { navController.navigate("team") },
                    onOpenPurchases = { navController.navigate("purchases") },
                )
            }
            composable("product/{slug}") { entry ->
                val slug = entry.arguments?.getString("slug") ?: return@composable
                ProductDetailScreen(slug = slug, onBack = { navController.popBackStack() })
            }
            composable("commissions") { CommissionsScreen() }
            composable("licenses") { LicensesScreen() }
            composable("menu") {
                MenuScreen(onNavigate = { route -> navController.navigate(route) })
            }
            composable("profile") {
                ProfileScreen(
                    onLoggedOut = {
                        navController.navigate("login") {
                            popUpTo(0) { inclusive = true }
                        }
                    },
                    onOpenWithdrawals = { navController.navigate("withdrawals") },
                )
            }

            // ===== Layar detail (di-push, punya tombol kembali) =====
            composable("purchases") {
                PurchasesScreen(onBack = { navController.popBackStack() })
            }
            composable("team") {
                TeamScreen(onBack = { navController.popBackStack() })
            }
            composable("coupons") {
                CouponsScreen(onBack = { navController.popBackStack() })
            }
            composable("team-purchases") {
                TeamPurchasesScreen(onBack = { navController.popBackStack() })
            }
            composable("withdrawals") {
                WithdrawalsScreen(onBack = { navController.popBackStack() })
            }
        }
    }
}

/** Dock navigasi timbul; tab terpilih tampak tenggelam ke dalam dock. */
@Composable
private fun SkeuoBottomBar(
    currentRoute: String?,
    onTabClick: (String) -> Unit,
) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .skeuoRaised(RoundedCornerShape(topStart = 24.dp, topEnd = 24.dp), elevation = 16.dp)
            .navigationBarsPadding()
            .padding(horizontal = 8.dp, vertical = 10.dp),
        horizontalArrangement = Arrangement.SpaceEvenly,
        verticalAlignment = Alignment.CenterVertically,
    ) {
        bottomTabs.forEach { tab ->
            val selected = currentRoute == tab.route
            var itemModifier = Modifier
                .weight(1f)
                .clickable(
                    interactionSource = remember { MutableInteractionSource() },
                    indication = null,
                ) { onTabClick(tab.route) }
            if (selected) {
                itemModifier = itemModifier.skeuoInset(RoundedCornerShape(16.dp))
            }

            Column(
                horizontalAlignment = Alignment.CenterHorizontally,
                modifier = itemModifier.padding(vertical = 8.dp),
            ) {
                Icon(
                    tab.icon,
                    contentDescription = tab.label,
                    tint = if (selected) IndigoLight else TextMuted,
                    modifier = Modifier.size(24.dp),
                )
                Spacer(Modifier.height(3.dp))
                Text(
                    tab.label,
                    style = MaterialTheme.typography.labelSmall,
                    color = if (selected) IndigoLight else TextMuted,
                    fontWeight = if (selected) FontWeight.Bold else FontWeight.Normal,
                )
            }
        }
    }
}
