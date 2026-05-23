"use client";

import React, { useState, useEffect, useRef } from "react";
import { useCart } from "@/context/CartContext";
import { motion, AnimatePresence } from "framer-motion";
import { checkoutApi, catalogApi, getImageUrl } from "@/lib/api";
import {
  Truck,
  Store as StoreIcon,
  MapPin,
  Clock,
  Receipt,
  ChevronRight,
  CheckCircle,
  CreditCard,
  QrCode,
  DollarSign,
  ArrowLeft,
  Plus
} from "lucide-react";
import Link from "next/link";
import AddressSidebar from "@/components/checkout/AddressSidebar";
import StoreSidebar from "@/components/checkout/StoreSidebar";
import PickupTimeSidebar from "@/components/checkout/PickupTimeSidebar";
import InvoiceSidebar from "@/components/checkout/InvoiceSidebar";
import VoucherSidebar from "@/components/catalog/VoucherSidebar";
import GiftSelectionPanel from "@/components/catalog/GiftSelectionPanel";
import { useRouter } from "next/navigation";


export default function CheckoutPage() {
  const {
    items, subtotal, totalBasePrice, totalProductDiscount,
    appliedVoucher, voucherDiscount, rewards, selectReward,
    setIsVoucherSidebarOpen, isVoucherSidebarOpen,
    appliedRedemptions, removeRedemption
  } = useCart();

  const router = useRouter();

  // State
  const [deliveryType, setDeliveryType] = useState<"shipping" | "pickup">("shipping");
  const [selectedAddress, setSelectedAddress] = useState<any>(null);
  const [selectedStore, setSelectedStore] = useState<any>(null);
  const [pickupTime, setPickupTime] = useState({ date: new Date(), slot: "09:00 - 11:00" });
  const [shippingMethod, setShippingMethod] = useState<string>("standard");
  const [shippingCost, setShippingCost] = useState<number>(19000);
  const [isInvoiceEnabled, setIsInvoiceEnabled] = useState(false);
  const [invoiceData, setInvoiceData] = useState<any>(null);
  const [paymentMethod, setPaymentMethod] = useState("cod");
  const [receiverName, setReceiverName] = useState("");
  const [receiverPhone, setReceiverPhone] = useState("");
  const [receiverErrors, setReceiverErrors] = useState<{ name?: string; phone?: string }>({});
  const [toast, setToast] = useState<{ message: string; visible: boolean }>({ message: "", visible: false });
  const [selectedRewardToChange, setSelectedRewardToChange] = useState<any | null>(null);
  const prevVoucherCode = useRef<string | null>(null);
  const isFirstRender = useRef(true);

  const showNotification = (msg: string) => {
    setToast({ message: msg, visible: true });
    setTimeout(() => setToast(prev => ({ ...prev, visible: false })), 2000);
  };

  // Watch voucher changes
  useEffect(() => {
    if (isFirstRender.current) {
      isFirstRender.current = false;
      prevVoucherCode.current = appliedVoucher?.code || null;
      return;
    }

    if (appliedVoucher?.code && appliedVoucher.code !== prevVoucherCode.current) {
      showNotification("Thêm voucher thành công");
    } else if (!appliedVoucher && prevVoucherCode.current) {
      showNotification("Đã gỡ voucher");
    }
    prevVoucherCode.current = appliedVoucher?.code || null;
  }, [appliedVoucher]);

  // Sidebars
  const [isAddressSidebarOpen, setIsAddressSidebarOpen] = useState(false);
  const [isStoreSidebarOpen, setIsStoreSidebarOpen] = useState(false);
  const [isPickupTimeSidebarOpen, setIsPickupTimeSidebarOpen] = useState(false);
  const [isInvoiceSidebarOpen, setIsInvoiceSidebarOpen] = useState(false);

  // Constants
  const [dbShippingMethods, setDbShippingMethods] = useState<any[]>([
    { id: 1, name: "Giao hàng tiêu chuẩn", provider: "standard", base_cost: 0 },
    { id: 2, name: "Giao nhanh 2H", provider: "fast_2h", base_cost: 15000 },
  ]);

  useEffect(() => {
    loadInitialData();
  }, []);

  const loadInitialData = async () => {
    try {
      const addrRes = await checkoutApi.getAddresses();
      if (addrRes.data?.length > 0) {
        setSelectedAddress(addrRes.data.find((a: any) => a.is_default) || addrRes.data[0]);
      }
      const storeRes = await catalogApi.getStores();
      if (storeRes.data?.length > 0) {
        setSelectedStore(storeRes.data[0]);
      }
      const shipRes = await catalogApi.getShippingMethods();
      if (shipRes.data?.length > 0) {
        setDbShippingMethods(shipRes.data);
      }
    } catch (e) {
      console.error("Failed to load initial data", e);
    }
  };

  const getExpectedDate = (type: string) => {
    const d = new Date();
    if (type === "standard") {
      d.setDate(d.getDate() + 3);
    } else if (type === "ghn") {
      d.setDate(d.getDate() + 2);
    } else {
      d.setDate(d.getDate() + 1);
    }
    return d.toLocaleDateString("vi-VN", { day: "2-digit", month: "2-digit", year: "numeric" });
  };

  useEffect(() => {
    const fetchFee = async () => {
      if (deliveryType !== "shipping") {
        setShippingCost(0);
        return;
      }
      try {
        const res = await catalogApi.calculateShippingFee({
          province: selectedAddress?.city,
          district: selectedAddress?.district,
          ward: selectedAddress?.ward,
          provider: shippingMethod
        });
        if (res.success) {
          setShippingCost(Number(res.fee));
        } else {
          const activeMethod = dbShippingMethods.find(m => m.provider === shippingMethod);
          setShippingCost(activeMethod ? Number(activeMethod.base_cost) : 0);
        }
      } catch (e) {
        console.error("Failed to calculate shipping fee", e);
        const activeMethod = dbShippingMethods.find(m => m.provider === shippingMethod);
        setShippingCost(activeMethod ? Number(activeMethod.base_cost) : 0);
      }
    };

    fetchFee();
  }, [selectedAddress, shippingMethod, deliveryType, dbShippingMethods]);

  const activeShippingMethod = dbShippingMethods.find(m => m.provider === shippingMethod);
  const finalTotal = Math.max(0, subtotal + (deliveryType === "shipping" ? shippingCost : 0));

  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleCheckout = async () => {
    if (isSubmitting) return;

    // Validate pickup receiver fields
    if (deliveryType === "pickup") {
      const errors: { name?: string; phone?: string } = {};
      if (!receiverName.trim()) {
        errors.name = "Vui lòng nhập họ và tên người nhận";
      } else if (receiverName.trim().length < 2) {
        errors.name = "Tên phải có ít nhất 2 ký tự";
      }
      const phoneRegex = /^(0|\+84)(3|5|7|8|9)\d{8}$/;
      if (!receiverPhone.trim()) {
        errors.phone = "Vui lòng nhập số điện thoại";
      } else if (!phoneRegex.test(receiverPhone.trim())) {
        errors.phone = "Số điện thoại không đúng định dạng (VD: 0912345678)";
      }
      if (Object.keys(errors).length > 0) {
        setReceiverErrors(errors);
        // Scroll to the error fields
        document.getElementById('pickup-receiver-section')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
      }
      setReceiverErrors({});
    }

    // Tổng hợp items: paid items + gift items (quà tặng, price = 0)
    const checkoutItems: Array<{
      product_id?: number | null;
      variant_id: number | null;
      marketing_gift_id?: number | null;
      quantity: number;
      is_gift: boolean;
    }> = [...items.map(i => ({ variant_id: i.variant_id, quantity: i.quantity, is_gift: false }))];
    
    // Thêm quà tặng từ rewards vào kiện hàng (giá = 0)
    rewards.forEach(reward => {
      let giftVariantId: any = null;
      let marketingGiftId: any = null;
      let isProductId = false;
      
      if (reward.type === 'gift_product') {
        giftVariantId = reward.selected_id || reward.id;
        isProductId = true;
      } else if (reward.type === 'gift_product_choice' || reward.type === 'choice') {
        const option = reward.selected_option;
        if (option) {
          if (option.item_type === 'gift') {
            marketingGiftId = option.id || option.item_id;
          } else {
            giftVariantId = option.id || option.item_id;
            isProductId = true;
          }
        } else {
          giftVariantId = reward.selected_id;
          isProductId = true;
        }
      }

      if (giftVariantId || marketingGiftId) {
        const giftQty = reward.quantity || 1;
        if (marketingGiftId) {
          checkoutItems.push({
            variant_id: null,
            marketing_gift_id: Number(marketingGiftId),
            quantity: giftQty,
            is_gift: true
          });
        } else {
          const vId = Number(giftVariantId);
          if (!isNaN(vId)) {
            checkoutItems.push({
              product_id: isProductId ? vId : null,
              variant_id: isProductId ? null : vId,
              quantity: giftQty,
              is_gift: true
            });
          }
        }
      }
    });

    console.log("SENDING CHECKOUT PAYLOAD:", {
      items_count: checkoutItems.length,
      items: checkoutItems
    });

    const payload: any = {
      items: checkoutItems,
      delivery_type: deliveryType,
      payment_method: paymentMethod,
      shipping_cost: Number(deliveryType === "shipping" ? shippingCost : 0),
      discount_amount: Number(voucherDiscount),
      voucher_code: appliedVoucher ? appliedVoucher.code : null,
      notes: "",
      applied_redemption_ids: appliedRedemptions.map(ar => ar.id),
    };

    if (deliveryType === "shipping") {
      if (!selectedAddress) return alert("Vui lòng chọn địa chỉ giao hàng");
      payload.shipping_address = selectedAddress;
      payload.shipping_method_id = activeShippingMethod ? activeShippingMethod.id : 1;
      payload.expected_delivery_date = getExpectedDate(shippingMethod).split("/").reverse().join("-");
    } else {
      if (!selectedStore) return alert("Vui lòng chọn cửa hàng");
      payload.store_id = selectedStore.id;
      payload.pickup_time = `${pickupTime.slot} ${pickupTime.date.toLocaleDateString("vi-VN")}`;
      payload.receiver_name = receiverName;
      payload.receiver_phone = receiverPhone;
    }

    if (isInvoiceEnabled && invoiceData) {
      payload.invoice_info = invoiceData;
    }

    setIsSubmitting(true);
    try {
      const res = await checkoutApi.checkout(payload);
      if (res.success) {
        localStorage.removeItem("vinamilk_cart");
        localStorage.removeItem("vinamilk_cart_redemptions");
        if (res.payment_url) {
          console.log("Redirecting to payment:", res.payment_url);
          window.location.href = res.payment_url;
        } else {
          window.location.href = `/account/orders/${res.order_number}`;
        }
      } else {
        alert(res.message || "Lỗi khi tạo đơn hàng");
        setIsSubmitting(false);
      }
    } catch (e) {
      console.error("Checkout Error:", e);
      alert("Đã có lỗi xảy ra. Vui lòng thử lại.");
      setIsSubmitting(false);
    }
  };

  const notebookBgStyle = {
    backgroundColor: "#f7f9fc",
    backgroundImage: `
      linear-gradient(rgba(2, 19, 176, 0.03) 1px, transparent 1px)
    `,
    backgroundSize: "100% 40px",
  };

  const horizontalLineStyle = {
    backgroundImage: `
      linear-gradient(to bottom, rgba(2, 19, 176, 0.06) 1.5px, transparent 1.5px),
      linear-gradient(to bottom, transparent 39px, rgba(2, 19, 176, 0.02) 39px, rgba(2, 19, 176, 0.02) 40px, transparent 40px),
      linear-gradient(to bottom, transparent 79px, rgba(2, 19, 176, 0.02) 79px, rgba(2, 19, 176, 0.02) 80px, transparent 80px),
      linear-gradient(to bottom, transparent 119px, rgba(2, 19, 176, 0.02) 119px, rgba(2, 19, 176, 0.02) 120px, transparent 120px),
      linear-gradient(to bottom, transparent 159px, rgba(2, 19, 176, 0.06) 159px, rgba(2, 19, 176, 0.06) 160px, transparent 160px)
    `,
    backgroundSize: "100% 160px",
  };

  if (items.length === 0) {
    return (
      <div className="min-h-[60vh] flex flex-col items-center justify-center p-10">
        <h1 className="text-2xl font-black text-[#002060] mb-4">Giỏ hàng đang trống</h1>
        <Link href="/" className="px-8 py-3 bg-[#0213b0] text-white font-bold">Quay về trang chủ</Link>
      </div>
    );
  }

  return (
    <div className="min-h-screen relative font-sans" style={{ backgroundColor: "#f7f9fc" }}>
      {/* Notebook Lines Overlay */}
      <div className="absolute inset-0 pointer-events-none" style={horizontalLineStyle} />

      {/* Sidebars Backdrop */}
      <AnimatePresence>
        {(isAddressSidebarOpen || isStoreSidebarOpen || isPickupTimeSidebarOpen || isInvoiceSidebarOpen || isVoucherSidebarOpen) && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 z-[1000] bg-black/30 backdrop-blur-[3px]"
            onClick={() => {
              setIsAddressSidebarOpen(false);
              setIsStoreSidebarOpen(false);
              setIsPickupTimeSidebarOpen(false);
              setIsInvoiceSidebarOpen(false);
              setIsVoucherSidebarOpen(false);
            }}
          />
        )}
      </AnimatePresence>

      {/* Custom Header */}
      <header className="relative z-10 h-20 flex items-center px-10 border-b border-[#0213b0]/5 bg-[#f7f9fc]/80 backdrop-blur-sm">
        <div className="flex-1">
          <button onClick={() => router.back()} className="flex items-center gap-2 text-[#0213b0] font-bold text-[14px] hover:translate-x-[-4px] transition-transform">
            <ArrowLeft size={18} />
            <span>Quay lại</span>
          </button>
        </div>
        <div className="flex-shrink-0">
          <Link href="/">
            <img src="/logo_vina.svg" alt="Vinamilk" className="h-10 w-auto" />
          </Link>
        </div>
        <div className="flex-1" />
      </header>

      <main className="relative z-10 max-w-[1300px] mx-auto px-10 py-12">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-10 items-start">

          {/* Left Column */}
          <div className="space-y-6">
            <h1 className="text-[42px] font-black text-[#002060] mb-2 leading-none">Thanh toán</h1>

            {/* 1. Account */}
            <div className="space-y-1">
              <h3 className="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Tài khoản tích điểm</h3>
              <div className="bg-[#fffff1] p-5 flex items-center justify-between border border-[#0213b0]/5">
                <div className="flex items-center gap-3">
                  <CheckCircle size={18} className="text-[#0213b0]" />
                  <p className="text-[15px] font-black text-[#002060]">Quý Vy Trọng • 0945449758</p>
                </div>
              </div>
            </div>

            {/* 2. Delivery Switcher */}
            <div className="grid grid-cols-2 gap-0 border border-[#0213b0]">
              <button
                onClick={() => setDeliveryType("shipping")}
                className={`flex items-center justify-center gap-3 py-4 transition-all ${deliveryType === "shipping" ? "bg-[#0213b0] text-white" : "bg-[#fffff1] text-[#0213b0]"
                  }`}
              >
                <Truck size={20} />
                <span className="font-black text-[14px]">Giao tận nơi</span>
              </button>
              <button
                onClick={() => setDeliveryType("pickup")}
                className={`flex items-center justify-center gap-3 py-4 transition-all ${deliveryType === "pickup" ? "bg-[#0213b0] text-white" : "bg-[#fffff1] text-[#0213b0]"
                  }`}
              >
                <StoreIcon size={20} />
                <span className="font-black text-[14px]">Nhận tại cửa hàng</span>
              </button>
            </div>

            {/* 3. Details */}
            {deliveryType === "shipping" ? (
              <div className="bg-[#fffff1] p-6 border border-[#0213b0]/5">
                <div className="flex items-center justify-between mb-4">
                  <h3 className="text-[11px] font-black text-gray-400 uppercase tracking-widest">Địa chỉ nhận hàng</h3>
                  <button onClick={() => setIsAddressSidebarOpen(true)} className="text-[12px] font-black text-[#0213b0] underline">Đổi</button>
                </div>
                {selectedAddress ? (
                  <div className="space-y-1">
                    <p className="font-black text-[#002060]">{selectedAddress.last_name} {selectedAddress.first_name} • {selectedAddress.phone}</p>
                    <p className="text-[14px] text-gray-500 leading-relaxed">
                      {selectedAddress.detail}, {selectedAddress.ward}, {selectedAddress.district}, {selectedAddress.city}
                    </p>
                  </div>
                ) : (
                  <p className="text-gray-400 italic">Vui lòng thêm địa chỉ nhận hàng</p>
                )}
              </div>
            ) : (
              <div className="space-y-6">
                <div id="pickup-receiver-section" className="grid grid-cols-2 gap-4">
                  <div className={`bg-[#fffff1] p-5 border ${receiverErrors.name ? 'border-red-400 bg-red-50' : 'border-[#0213b0]/5'} transition-colors`}>
                    <label className={`text-[10px] font-bold uppercase mb-2 block ${receiverErrors.name ? 'text-red-500' : 'text-gray-400'}`}>Người nhận *</label>
                    <input
                      type="text"
                      value={receiverName}
                      onChange={(e) => {
                        setReceiverName(e.target.value);
                        if (e.target.value.trim().length >= 2) setReceiverErrors(prev => ({ ...prev, name: undefined }));
                      }}
                      placeholder="Họ và tên *"
                      className={`w-full border-b py-1 outline-none text-[14px] font-bold text-[#002060] bg-transparent transition-colors ${receiverErrors.name ? 'border-red-400 placeholder-red-300' : 'border-gray-100 focus:border-[#0213b0]'}`}
                    />
                    {receiverErrors.name && (
                      <p className="text-[11px] text-red-500 font-bold mt-1.5">{receiverErrors.name}</p>
                    )}
                  </div>
                  <div className={`bg-[#fffff1] p-5 border ${receiverErrors.phone ? 'border-red-400 bg-red-50' : 'border-[#0213b0]/5'} transition-colors`}>
                    <label className={`text-[10px] font-bold uppercase mb-2 block ${receiverErrors.phone ? 'text-red-500' : 'text-gray-400'}`}>Số điện thoại *</label>
                    <input
                      type="tel"
                      value={receiverPhone}
                      onChange={(e) => {
                        setReceiverPhone(e.target.value);
                        const phoneRegex = /^(0|\+84)(3|5|7|8|9)\d{8}$/;
                        if (phoneRegex.test(e.target.value.trim())) setReceiverErrors(prev => ({ ...prev, phone: undefined }));
                      }}
                      placeholder="Số điện thoại *"
                      className={`w-full border-b py-1 outline-none text-[14px] font-bold text-[#002060] bg-transparent transition-colors ${receiverErrors.phone ? 'border-red-400 placeholder-red-300' : 'border-gray-100 focus:border-[#0213b0]'}`}
                    />
                    {receiverErrors.phone && (
                      <p className="text-[11px] text-red-500 font-bold mt-1.5">{receiverErrors.phone}</p>
                    )}
                  </div>
                </div>
                <div className="bg-[#fffff1] p-6 border border-[#0213b0]/5">
                  <div className="flex items-center justify-between mb-4">
                    <h3 className="text-[11px] font-black text-gray-400 uppercase tracking-widest">Nhận tại cửa hàng</h3>
                    <button onClick={() => setIsStoreSidebarOpen(true)} className="text-[12px] font-black text-[#0213b0] underline">Đổi</button>
                  </div>
                  <div className="flex items-start gap-3">
                    <MapPin className="text-[#0213b0] mt-1 shrink-0" size={18} />
                    <div>
                      <p className="font-black text-[#002060] text-[14px]">{selectedStore?.name || "Chọn cửa hàng"}</p>
                      <p className="text-[13px] text-gray-500 mt-1">{selectedStore?.address}, {selectedStore?.ward}, {selectedStore?.district}, {selectedStore?.province}</p>
                    </div>
                  </div>
                </div>
                <div className="bg-[#fffff1] p-6 border border-[#0213b0]/5">
                  <div className="flex items-center justify-between mb-4">
                    <h3 className="text-[11px] font-black text-gray-400 uppercase tracking-widest">Dự kiến nhận hàng</h3>
                    <button onClick={() => setIsPickupTimeSidebarOpen(true)} className="text-[12px] font-black text-[#0213b0] underline">Đổi</button>
                  </div>
                  <div className="flex items-center gap-3">
                    <Clock className="text-[#0213b0]" size={18} />
                    <p className="text-[14px] font-black text-[#002060]">
                      {pickupTime.slot} • {pickupTime.date.toLocaleDateString("vi-VN", { weekday: "long", day: "2-digit", month: "2-digit" })}
                    </p>
                  </div>
                </div>
              </div>
            )}

            {/* 4. Shipping Methods */}
            {deliveryType === "shipping" && (
              <div className="space-y-1">
                <h3 className="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Phương thức giao nhận</h3>
                <div className="space-y-0 border border-[#0213b0]/10 overflow-hidden">
                  {dbShippingMethods.map((method: any) => (
                    <label
                      key={method.id}
                      className={`flex items-center justify-between p-5 transition-all cursor-pointer border-b last:border-0 ${shippingMethod === method.provider ? "bg-[#e9f0f8]" : "bg-[#fffff1]"
                        }`}
                    >
                      <div className="flex items-center gap-3">
                        <input
                          type="radio"
                          checked={shippingMethod === method.provider}
                          onChange={() => setShippingMethod(method.provider as any)}
                          className="w-4 h-4 text-[#0213b0]"
                        />
                        <span className="text-[14px] font-bold text-[#002060]">
                          {method.name} ({
                            method.provider === "standard"
                              ? getExpectedDate("standard")
                              : method.provider === "fast_2h"
                                ? `10:00 ${getExpectedDate("fast_2h")}`
                                : getExpectedDate("ghn")
                          })
                        </span>
                      </div>
                      <span className="font-black text-[#002060] text-[14px]">
                        {(() => {
                          const cost = shippingMethod === method.provider 
                            ? shippingCost 
                            : (method.provider === "ghn" ? 25000 : Number(method.base_cost));
                          return cost === 0 ? "Tính toán..." : `${cost.toLocaleString()}đ`;
                        })()}
                      </span>
                    </label>
                  ))}
                </div>
              </div>
            )}


            {/* 5. Invoice */}
            <div className="bg-[#fffff1] p-5 border border-[#0213b0]/5 flex items-center justify-between">
              <div className="flex items-center gap-3">
                <Receipt className="text-[#0213b0]" size={20} />
                <span className="text-[14px] font-black text-[#002060]">Yêu cầu xuất hoá đơn điện tử</span>
              </div>
              <button
                onClick={() => {
                  if (!invoiceData) {
                    setIsInvoiceSidebarOpen(true);
                  } else {
                    setIsInvoiceEnabled(!isInvoiceEnabled);
                  }
                }}
                className={`w-12 h-6 rounded-full transition-all relative ${isInvoiceEnabled ? "bg-[#0213b0]" : "bg-gray-200"}`}
              >
                <div className={`absolute top-1 w-4 h-4 bg-white rounded-full transition-all ${isInvoiceEnabled ? "left-7" : "left-1"}`} />
              </button>
            </div>

            {/* 6. Payment */}
            <div className="space-y-1">
              <h3 className="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Phương thức thanh toán</h3>
              <div className="space-y-0 border border-[#0213b0]/10 overflow-hidden">
                {[
                  { id: "cod", label: "Thanh toán khi nhận hàng (COD)", icon: DollarSign },
                  { id: "momo", label: "Ví điện tử Momo", icon: QrCode },
                  { id: "vnpay", label: "Internet Banking VNPay, ATM, ngân hàng", icon: CreditCard },
                  { id: "stripe", label: "Cổng thanh toán quốc tế Stripe (Visa/Master)", icon: CreditCard },
                  { id: "paypal", label: "Cổng thanh toán quốc tế PayPal", icon: CreditCard },
                ].map((pm) => (
                  <label
                    key={pm.id}
                    className={`flex items-center gap-3 p-4 transition-all cursor-pointer border-b last:border-0 ${paymentMethod === pm.id ? "bg-[#e9f0f8]" : "bg-[#fffff1]"
                      }`}
                  >
                    <input
                      type="radio"
                      checked={paymentMethod === pm.id}
                      onChange={() => setPaymentMethod(pm.id)}
                      className="w-4 h-4 text-[#0213b0]"
                    />
                    <pm.icon size={18} className="text-[#002060]" />
                    <span className="text-[14px] font-bold text-[#002060]">{pm.label}</span>
                  </label>
                ))}
              </div>
            </div>
          </div>

          {/* Right Column */}
          <div className="space-y-6">
            <div className="bg-[#fffff1] border border-[#0213b0]/10 overflow-hidden min-h-[600px] flex flex-col">
              <div className="p-8 flex-grow">
                <div className="flex items-center justify-between mb-8">
                  <h3 className="text-[20px] font-black text-[#002060] italic serif leading-tight">Kiện hàng 1</h3>
                  <span className="px-3 py-1 bg-[#e9f0f8] text-[11px] font-black text-[#0213b0] uppercase tracking-tighter">
                    {deliveryType === "pickup"
                      ? "Lấy tại cửa hàng"
                      : (dbShippingMethods.find(m => m.provider === shippingMethod)?.name || (shippingMethod === "standard" ? "Giao hàng tiêu chuẩn" : "Giao nhanh 2H"))}
                  </span>
                </div>

                <div className="space-y-6">
                  {items.map((item) => (
                    <div key={item.id} className="flex gap-4">
                      <div className="w-20 h-20 flex-shrink-0 flex items-center justify-center p-2 relative">
                        <img
                          src={getImageUrl(item.variant.main_image || item.product.main_image) || ""}
                          className="w-full h-full object-contain"
                        />
                        <span className="absolute -top-2 -right-2 bg-[#0213b0] text-white text-[11px] font-black w-6 h-6 rounded-full flex items-center justify-center border-2 border-white">
                          {item.quantity}
                        </span>
                      </div>
                      <div className="flex-grow min-w-0">
                        <p className="text-[15px] font-bold text-[#002060] line-clamp-2 leading-tight">{item.product.name}</p>
                        <p className="text-[11px] text-gray-400 mt-1 uppercase font-bold">{item.variant.volume} - {item.variant.packaging_type}</p>
                      </div>
                      <div className="text-right flex-shrink-0">
                        <p className="text-[13px] text-gray-400 line-through">{(item.variant.base_price * item.quantity).toLocaleString()}đ</p>
                        <p className="text-[16px] font-black text-[#002060]">{(item.variant.price * item.quantity).toLocaleString()}đ</p>
                      </div>
                    </div>
                  ))}

                  {/* Gifts */}
                  {rewards.map((reward, i) => (
                    <div key={i} className="flex gap-4 bg-[#e7f9a1] p-3 border border-[#e7f9a1]">
                      <div className="w-12 h-12 bg-white flex-shrink-0 flex items-center justify-center p-2 relative border border-[#e7f9a1]">
                        <img src={getImageUrl(reward.image || reward.selected_option?.image) || ""} className="w-full h-full object-contain" />
                        <span className="absolute -top-2 -right-2 bg-[#0213b0] text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center">
                          {reward.quantity || 1}
                        </span>
                      </div>
                      <div className="flex-grow min-w-0">
                        <p className="text-[13px] font-black text-[#0213b0] line-clamp-1 uppercase tracking-tighter">
                          {reward.name || reward.selected_option?.name}
                        </p>
                        <span className="text-[10px] text-[#0213b0]/60 font-bold uppercase">Quà tặng</span>
                      </div>
                      <div className="flex items-center">
                        <button
                          onClick={() => setSelectedRewardToChange(reward)}
                          className="text-[11px] font-black text-v-navy"
                        >
                          Đổi
                        </button>
                      </div>

                    </div>
                  ))}

                  {/* Personal Redeemed Gifts */}
                  {appliedRedemptions
                    .filter((ar) => ar.reward && ar.reward.type === "gift")
                    .map((ar, i) => (
                      <div key={`personal-gift-${i}`} className="flex gap-4 bg-[#e1faf2] p-3 border border-[#22c55e]/20">
                        <div className="w-12 h-12 bg-white flex-shrink-0 flex items-center justify-center p-2 relative border border-[#22c55e]/15">
                          <img
                            src={getImageUrl(ar.reward.image) || ar.reward.image || ""}
                            className="w-full h-full object-contain"
                          />
                          <span className="absolute -top-2 -right-2 bg-[#0213b0] text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center">
                            1
                          </span>
                        </div>
                        <div className="flex-grow min-w-0">
                          <p className="text-[13px] font-black text-green-700 line-clamp-1 uppercase tracking-tighter">
                            {ar.reward.name}
                          </p>
                          <span className="text-[10px] text-green-600 font-bold uppercase">Quà quy đổi điểm</span>
                        </div>
                        <div className="flex items-center">
                          <button
                            onClick={() => removeRedemption(ar.id)}
                            className="text-[11px] font-black text-red-500 hover:text-red-700 transition-colors"
                          >
                            Gỡ
                          </button>
                        </div>
                      </div>
                    ))}
                </div>
              </div>

              {/* Summary Section */}
              <div className="relative">
                <svg viewBox="0 0 400 20" className="w-full h-5" preserveAspectRatio="none">
                  <path d="M0 20 L0 10 C 20 0, 40 20, 60 10 C 80 0, 100 20, 120 10 C 140 0, 160 20, 180 10 C 200 0, 220 20, 240 10 C 260 0, 280 20, 300 10 C 320 0, 340 20, 360 10 C 380 0, 400 20, 400 10 L 400 20 Z" fill="#e9f0f8" />
                </svg>
                <div className="px-8 pt-4 pb-8 space-y-4 bg-[#e9f0f8]">
                  <div className="flex justify-between text-[14px] text-v-navy">
                    <span className="font-bold">Tổng tiền hàng</span>
                    <span className="font-black text-[16px] text-v-navy">{totalBasePrice.toLocaleString()}đ</span>
                  </div>
                  <div className="flex justify-between text-[14px] text-v-navy">
                    <span className="font-bold">Giảm giá sản phẩm</span>
                    <span className="font-black text-[16px] text-red-500">-{totalProductDiscount.toLocaleString()}đ</span>
                  </div>
                  <div className="flex justify-between text-[14px] text-v-navy">
                    <span className="font-bold">Phí vận chuyển</span>
                    <span className="font-black text-[16px] text-v-navy">{shippingCost === 0 ? "0đ" : `${shippingCost.toLocaleString()}đ`}</span>
                  </div>
                  <div className="flex justify-between text-[14px] text-v-navy">
                    <span className="font-bold">Voucher giảm giá</span>
                    <span className="font-black text-[16px] text-red-500">-{voucherDiscount.toLocaleString()}đ</span>
                  </div>

                  <div className="pt-4">
                    <div
                      onClick={() => setIsVoucherSidebarOpen(true)}
                      className={`px-5 py-4 flex items-center justify-between cursor-pointer transition-all border ${appliedVoucher ? "bg-[#e1faf2] border-[#22c55e]/30" : "bg-[#fffff1] border-[#0213b0]/10"
                        }`}
                    >
                      <div className="flex items-center gap-3">
                        <CheckCircle size={20} className={appliedVoucher ? "text-green-500" : "text-gray-200"} />
                        <span className={`text-[14px] font-black ${appliedVoucher ? "text-green-700" : "text-[#002060]"}`}>
                          {appliedVoucher ? `Đã áp dụng 1 voucher` : "Chưa áp dụng voucher"}
                        </span>
                      </div>
                      <Plus size={20} className={appliedVoucher ? "text-green-500" : "text-[#0213b0]"} />
                    </div>
                  </div>

                  <div className="flex justify-between items-center pt-4 border-t border-[#0213b0]/10">
                    <span className="text-[18px] font-black text-v-navy">Tổng</span>
                    <span className="text-[26px] font-black text-v-navy">{finalTotal.toLocaleString()}đ</span>
                  </div>

                  <button
                    onClick={handleCheckout}
                    disabled={isSubmitting}
                    className={`w-full py-5 bg-[#0213b0] text-white font-black text-[17px] mt-4 uppercase tracking-widest hover:bg-[#002060] transition-colors ${isSubmitting ? "opacity-70 cursor-not-allowed" : ""}`}
                  >
                    {isSubmitting ? "Đang xử lý..." : "Thanh toán"}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>

      {/* Sidebars */}
      <AddressSidebar
        isOpen={isAddressSidebarOpen}
        onClose={() => setIsAddressSidebarOpen(false)}
        selectedAddressId={selectedAddress?.id}
        onSelect={(addr) => setSelectedAddress(addr)}
      />
      <StoreSidebar
        isOpen={isStoreSidebarOpen}
        onClose={() => setIsStoreSidebarOpen(false)}
        selectedStoreId={selectedStore?.id}
        onSelect={(store) => setSelectedStore(store)}
      />
      <PickupTimeSidebar
        isOpen={isPickupTimeSidebarOpen}
        onClose={() => setIsPickupTimeSidebarOpen(false)}
        onSelect={(date, slot) => setPickupTime({ date, slot })}
        initialDate={pickupTime.date}
        initialTimeSlot={pickupTime.slot}
      />
      <InvoiceSidebar
        isOpen={isInvoiceSidebarOpen}
        onClose={() => setIsInvoiceSidebarOpen(false)}
        onApply={(data) => {
          setInvoiceData(data);
          setIsInvoiceEnabled(true);
          showNotification("Lưu thông tin hóa đơn thành công");
        }}
        initialData={invoiceData}
      />
      <VoucherSidebar
        isOpen={isVoucherSidebarOpen}
        onClose={() => setIsVoucherSidebarOpen(false)}
      />

      {selectedRewardToChange && (
        <GiftSelectionPanel
          isOpen={!!selectedRewardToChange}
          reward={selectedRewardToChange}
          onClose={() => setSelectedRewardToChange(null)}
          onApply={(selectionIds) => {
            if (selectedRewardToChange?.rule_id) {
              selectReward(selectedRewardToChange.rule_id, selectionIds);
            }
            setSelectedRewardToChange(null);
          }}
          isStandalone={true}
        />
      )}

      {/* Toast Notification */}

      <AnimatePresence>
        {toast.visible && (
          <motion.div
            initial={{ y: 100, opacity: 0, x: "-50%" }}
            animate={{ y: -40, opacity: 1, x: "-50%" }}
            exit={{ y: 100, opacity: 0, x: "-50%" }}
            className="fixed bottom-0 left-1/2 z-[2000] px-6 py-2.5 bg-[#e7f9a1] rounded-full shadow-lg flex items-center gap-3 border border-[#0213b0]/10"
          >
            <div className="flex items-center justify-center w-5 h-5 rounded-full border-2 border-[#0213b0]">
              <CheckCircle size={14} className="text-[#0213b0]" />
            </div>
            <span className="text-[14px] font-black text-[#0213b0] whitespace-nowrap">{toast.message}</span>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}
