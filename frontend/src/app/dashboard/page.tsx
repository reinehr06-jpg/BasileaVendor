"use client";

import { useAuth } from "@/context/AuthContext";
import { redirect } from "next/navigation";
import MasterDashboard from "./MasterDashboard";
import GestorDashboard from "./GestorDashboard";
import VendedorDashboard from "./VendedorDashboard";

export default function DashboardPage() {
  const { user, isLoading } = useAuth();
  
  if (isLoading) return <div className="flex h-screen items-center justify-center">Carregando...</div>;
  if (!user) redirect("/auth/login");
  if (user.perfil === "master") {
    return <MasterDashboard />;
  } else if (user.perfil === "gestor") {
    return <GestorDashboard />;
  } else {
    return <VendedorDashboard />;
  }
}
