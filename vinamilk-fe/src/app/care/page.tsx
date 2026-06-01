import React from "react";
import { careApi } from "@/lib/api";
import { CarePageClient } from "@/components/care/CarePageClient";

export const dynamic = "force-dynamic";

export const metadata = {
  title: "Vinamilk Care | Vinamilk",
  description: "Gói sữa định kỳ — chăm sóc người thân mỗi tháng.",
};

export default async function CareLandingPage() {
  let settings: Awaited<ReturnType<typeof careApi.getPage>>["settings"] | null = null;
  let initialProducts: import("@/types/care").CareProduct[] = [];
  try {
    const data = await careApi.getPage();
    settings = data.settings;
  } catch {
    settings = null;
  }
  try {
    const data = await careApi.getProducts();
    initialProducts = data.products || [];
  } catch {
    initialProducts = [];
  }

  return <CarePageClient initialSettings={settings} initialProducts={initialProducts} />;
}
