import { CareCartProvider } from "@/context/CareCartContext";

export default function CareLayout({ children }: { children: React.ReactNode }) {
  return <CareCartProvider>{children}</CareCartProvider>;
}
