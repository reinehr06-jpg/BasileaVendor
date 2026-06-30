import { useState, useEffect } from "react";
import { MembrosService } from "@/services/membros.service";
import type { Membro } from "@/types/membro";

export function useMembros(params?: { page?: number; search?: string }) {
  const [data, setData]       = useState<Membro[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError]     = useState<string | null>(null);

  useEffect(() => {
    setLoading(true);
    MembrosService.list(params)
      .then(res => setData(res.data))
      .catch(err => setError(err.message))
      .finally(() => setLoading(false));
  }, [params?.page, params?.search]);

  return { data, loading, error };
}
