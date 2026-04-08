import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import { RouterProvider } from "react-router-dom";
import { FournisseurAuth } from "./contexts";
import "./index.css";
import "./App.css";
import { router } from "./routes";

createRoot(document.getElementById("root")!).render(
  <StrictMode>
    <FournisseurAuth>
      <RouterProvider router={router} />
    </FournisseurAuth>
  </StrictMode>
);
