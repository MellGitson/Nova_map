import { createBrowserRouter, Navigate } from "react-router-dom";
import { ProtectedRoute, GuestOnlyRoute } from "./components";
import { RootLayout } from "./layouts";
import {
  HomePage,
  LoginPage,
  RegisterPage,
  RegisterConfirmPage,
  DashboardPage,
  CreerProjetPage,
  ListeProjetsPage,
  VueGraphePage,
  DetailComposantPage,
} from "./views";

export const router = createBrowserRouter([
  {
    path: "/",
    element: <RootLayout />,
    children: [
      // -----------------------------------------------------------------------
      // Pages publiques
      // -----------------------------------------------------------------------
      {
        index: true,
        element: (
          <GuestOnlyRoute>
            <HomePage />
          </GuestOnlyRoute>
        ),
      },
      { path: "login",             element: <LoginPage /> },
      { path: "register",          element: <RegisterPage /> },
      { path: "register/confirm",  element: <RegisterConfirmPage /> },

      // -----------------------------------------------------------------------
      // Pages protégées (utilisateur connecté requis)
      // -----------------------------------------------------------------------
      {
        path: "dashboard",
        element: (
          <ProtectedRoute>
            <DashboardPage />
          </ProtectedRoute>
        ),
      },

      // Projets
      {
        path: "projets",
        element: (
          <ProtectedRoute>
            <ListeProjetsPage />
          </ProtectedRoute>
        ),
      },
      {
        path: "projets/nouveau",
        element: (
          <ProtectedRoute>
            <CreerProjetPage />
          </ProtectedRoute>
        ),
      },
      {
        path: "projets/:projetId",
        element: (
          <ProtectedRoute>
            <VueGraphePage />
          </ProtectedRoute>
        ),
      },

      // Composants (détail depuis le graphe)
      {
        path: "projets/:projetId/composants/:composantId",
        element: (
          <ProtectedRoute>
            <DetailComposantPage />
          </ProtectedRoute>
        ),
      },

      // Fallback
      { path: "*", element: <Navigate to="/" replace /> },
    ],
  },
]);
