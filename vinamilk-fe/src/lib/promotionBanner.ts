import { PromotionBanner } from "@/types";

/** Khớp banner theo ?banner=id hoặc hash #slug trong URL */
export function findPromotionBannerFromUrl(
  banners: PromotionBanner[],
  bannerIdParam: string | null,
  hash: string
): PromotionBanner | null {
  if (bannerIdParam) {
    const id = Number(bannerIdParam);
    if (!Number.isNaN(id)) {
      const byId = banners.find((b) => b.id === id);
      if (byId) return byId;
    }
  }

  if (!hash) return null;

  const decoded = decodeURIComponent(hash).toLowerCase();

  return (
    banners.find((b) => {
      const link = b.link_url?.toLowerCase() ?? "";
      if (link.includes(`#${decoded}`) || link.endsWith(`/${decoded}`)) return true;
      return slugify(b.title) === decoded;
    }) ?? null
  );
}

function slugify(value: string): string {
  return value
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .toLowerCase()
    .replace(/đ/g, "d")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "");
}
