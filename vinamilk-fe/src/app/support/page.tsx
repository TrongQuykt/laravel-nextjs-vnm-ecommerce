import { redirect } from "next/navigation";

export default function Page() {
  // Automatically redirect to the privacy policy page as the default support landing
  redirect("/support/privacy-policy");
}
