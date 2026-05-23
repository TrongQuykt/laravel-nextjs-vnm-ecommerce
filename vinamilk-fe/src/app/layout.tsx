import type { Metadata } from "next";
import { Playfair_Display, Montserrat } from "next/font/google";
import "./globals.css";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import ChatWidget from "@/components/chat/ChatWidget";
import MainLayoutWrapper from "@/components/layout/MainLayoutWrapper";
 
const playfair = Playfair_Display({
  variable: "--font-serif",
  subsets: ["latin", "vietnamese"],
  display: "swap",
});
 
const montserrat = Montserrat({
  variable: "--font-sans",
  subsets: ["latin", "vietnamese"],
  weight: ["300", "400", "500", "600", "700", "800", "900"],
  display: "swap",
});
 
export const metadata: Metadata = {
  title: "Vinamilk - Giấc Mơ Sữa Việt",
  description: "Cửa hàng trà sữa, sữa bột, sữa chua và các sản phẩm dinh dưỡng từ Vinamilk.",
};
 
import { CartProvider } from "@/context/CartContext";
import { FlyToCart } from "@/components/catalog/FlyToCart";
import CartSidebar from "@/components/catalog/CartSidebar";

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html
      lang="vi"
      className={`${playfair.variable} ${montserrat.variable} h-full antialiased`}
    >
      <body className="min-h-full flex flex-col font-sans selection:bg-v-blue selection:text-white">
        <CartProvider>
          <MainLayoutWrapper>
            {children}
          </MainLayoutWrapper>
          <FlyToCart />
          <CartSidebar />
        </CartProvider>
      </body>
    </html>
  );
}
