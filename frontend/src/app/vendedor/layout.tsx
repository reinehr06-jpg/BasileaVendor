"use client";

import React from "react";
import Sidebar from "@/components/Sidebar";
import Topbar from "@/components/Topbar";

export default function VendedorLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="flex min-h-screen bg-[#F5F5F7] font-inter overflow-x-hidden">
      <Sidebar />
      <div className="flex-1 ml-[240px] flex flex-col min-h-screen relative pb-[80px] overflow-x-hidden">
        <Topbar />
        {children}
      </div>
    </div>
  );
}
