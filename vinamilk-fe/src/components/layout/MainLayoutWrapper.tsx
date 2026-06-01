"use client";

import { usePathname } from "next/navigation";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import ChatWidget from "@/components/chat/ChatWidget";

export default function MainLayoutWrapper({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();
  const isCheckout = pathname === "/checkout" || pathname === "/care/thanh-toan";

  return (
    <>
      {!isCheckout && <Navbar />}
      <main className="flex-grow">{children}</main>
      {!isCheckout && <Footer />}
      {!isCheckout && <ChatWidget />}
    </>
  );
}
