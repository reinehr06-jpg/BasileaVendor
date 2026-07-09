"use client";

import React, { useState } from "react";
import { AuthSplitLayout } from "@/components/auth/AuthSplitLayout";
import { LoginForm } from "@/components/auth/LoginForm";

export default function LoginPage() {
  const [isRegistering, setIsRegistering] = useState(false);
  return (
    <AuthSplitLayout>
      <LoginForm setIsRegistering={setIsRegistering} />
    </AuthSplitLayout>
  );
}
