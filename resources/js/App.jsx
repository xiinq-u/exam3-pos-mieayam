import { BrowserRouter, Route, Routes } from "react-router-dom";
import AppShell from "./components/AppShell";
import RequireRole from "./components/RequireRole";
import RoleHomeRedirect from "./components/RoleHomeRedirect";
import SessionValidator from "./components/SessionValidator";
import AccountPage from "./pages/AccountPage";
import AuditLogPage from "./pages/AuditLogPage";
import CashierDashboardPage from "./pages/cashier/CashierDashboardPage";
import CashierTransactionPage from "./pages/cashier/CashierTransactionPage";
import FinancePage from "./pages/finance/FinancePage";
import KitchenDashboardPage from "./pages/kitchen/KitchenDashboardPage";
import KitchenOrdersPage from "./pages/kitchen/KitchenOrdersPage";
import LoginPage from "./pages/loginpage";
import MaterialPage from "./pages/MaterialPage";
import OrderManagementPage from "./pages/order/OrderManagementPage";
import OrderDetails from "./pages/order/OrderDetails";
import PendingOrders from "./pages/order/PendingOrders";
import ProductCreate from "./pages/product/ProductCreate";
import ProductEdit from "./pages/product/ProductEdit";
import ProductIndex from "./pages/product/ProductIndex";
import ProductShow from "./pages/product/ProductShow";
import ReportPage from "./pages/ReportPage";
import UserManagementPage from "./pages/UserManagementPage";
import OwnerDashboardPage from "./pages/owner/OwnerDashboardPage";

export default function App() {
    return (
        <BrowserRouter basename="/react">
            <SessionValidator>
                <Routes>
                    <Route path="/login" element={<LoginPage />} />
                    <Route path="/" element={<RoleHomeRedirect />} />
                    <Route element={<AppShell />}>
                        <Route path="/owner" element={<RequireRole roles={["owner"]}><OwnerDashboardPage /></RequireRole>} />
                        <Route path="/cashier" element={<RequireRole roles={["cashier"]}><CashierDashboardPage /></RequireRole>} />
                        <Route path="/cashier/transaction" element={<RequireRole roles={["cashier"]}><CashierTransactionPage /></RequireRole>} />
                        <Route path="/shifts" element={<RequireRole roles={["cashier"]}><CashierDashboardPage /></RequireRole>} />
                        <Route path="/kitchen" element={<RequireRole roles={["kitchen"]}><KitchenDashboardPage /></RequireRole>} />
                        <Route path="/kitchen/orders" element={<RequireRole roles={["kitchen"]}><KitchenOrdersPage /></RequireRole>} />
                        <Route path="/materials" element={<RequireRole roles={["owner", "kitchen"]}><MaterialPage /></RequireRole>} />
                        <Route path="/products" element={<RequireRole roles={["owner"]}><ProductIndex /></RequireRole>} />
                        <Route path="/products/create" element={<RequireRole roles={["owner"]}><ProductCreate /></RequireRole>} />
                        <Route path="/products/:productId/edit" element={<RequireRole roles={["owner"]}><ProductEdit /></RequireRole>} />
                        <Route path="/products/:productId" element={<RequireRole roles={["owner"]}><ProductShow /></RequireRole>} />
                        <Route path="/users" element={<RequireRole roles={["owner"]}><UserManagementPage /></RequireRole>} />
                        <Route path="/reports" element={<RequireRole roles={["owner"]}><ReportPage /></RequireRole>} />
                        <Route path="/finance" element={<RequireRole roles={["owner"]}><FinancePage /></RequireRole>} />
                        <Route path="/orders" element={<RequireRole roles={["cashier"]}><PendingOrders /></RequireRole>} />
                        <Route path="/orders/manage" element={<RequireRole roles={["owner"]}><OrderManagementPage /></RequireRole>} />
                        <Route path="/orders/:orderId" element={<RequireRole roles={["cashier"]}><OrderDetails /></RequireRole>} />
                        <Route path="/audit-logs" element={<RequireRole roles={["owner"]}><AuditLogPage /></RequireRole>} />
                        <Route path="/account" element={<RequireRole roles={["owner", "cashier", "kitchen"]}><AccountPage /></RequireRole>} />
                    </Route>
                    <Route path="*" element={<RoleHomeRedirect />} />
                </Routes>
            </SessionValidator>
        </BrowserRouter>
    );
}
