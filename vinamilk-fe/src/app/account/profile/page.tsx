'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import VInput from '@/components/ui/VInput';
import VButton from '@/components/ui/VButton';
import VCheckbox from '@/components/ui/VCheckbox';
import { Copy } from 'lucide-react';

export default function ProfilePage() {
  const [activeTab, setActiveTab] = useState<'info' | 'password'>('info');
  const [user, setUser] = useState<any>(null);
  
  // Info State
  const [name, setName] = useState('');
  const [phone, setPhone] = useState('');
  const [agreeAds, setAgreeAds] = useState(false);
  const [agreeSurvey, setAgreeSurvey] = useState(false);
  const [isSavingInfo, setIsSavingInfo] = useState(false);
  const [infoMessage, setInfoMessage] = useState('');

  // Password State
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [isSavingPassword, setIsSavingPassword] = useState(false);
  const [passwordMessage, setPasswordMessage] = useState({ type: '', text: '' });

  useEffect(() => {
    // In a real app, we would fetch fresh user data from /api/v1/user
    const storedUser = localStorage.getItem('user');
    if (storedUser) {
      const parsed = JSON.parse(storedUser);
      setUser(parsed);
      setName(parsed.name || '');
      setPhone(parsed.phone || '');
    }
  }, []);

  const handleSaveInfo = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSavingInfo(true);
    setInfoMessage('');
    try {
      const token = localStorage.getItem('auth_token');
      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000/api/v1'}/user/profile`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({ name })
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.message || 'Lỗi cập nhật');
      
      setInfoMessage('Cập nhật thành công');
      localStorage.setItem('user', JSON.stringify(data.user));
    } catch (err: any) {
      setInfoMessage(err.message);
    } finally {
      setIsSavingInfo(false);
    }
  };

  const handleSavePassword = async (e: React.FormEvent) => {
    e.preventDefault();
    if (newPassword !== confirmPassword) {
      setPasswordMessage({ type: 'error', text: 'Mật khẩu xác nhận không khớp' });
      return;
    }
    setIsSavingPassword(true);
    setPasswordMessage({ type: '', text: '' });
    try {
      const token = localStorage.getItem('auth_token');
      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000/api/v1'}/user/password`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({ 
          current_password: currentPassword, 
          password: newPassword,
          password_confirmation: confirmPassword
        })
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.message || (Object.values(data.errors || {}) as string[][])[0]?.[0] || 'Lỗi đổi mật khẩu');
      
      setPasswordMessage({ type: 'success', text: 'Đổi mật khẩu thành công' });
      setCurrentPassword('');
      setNewPassword('');
      setConfirmPassword('');
    } catch (err: any) {
      setPasswordMessage({ type: 'error', text: err.message });
    } finally {
      setIsSavingPassword(false);
    }
  };

  return (
    <div className="w-full">
      {/* Tabs */}
      <div className="flex border-b border-[#002094]/10 mb-8">
        <button
          className={`pb-4 px-2 mr-8 text-[16px] font-bold transition-all ${
            activeTab === 'info' 
              ? 'text-[#002094] border-b-2 border-[#002094]' 
              : 'text-[#002094]/50 hover:text-[#002094]'
          }`}
          onClick={() => setActiveTab('info')}
        >
          Thông tin của tôi
        </button>
        <button
          className={`pb-4 px-2 text-[16px] font-bold transition-all ${
            activeTab === 'password' 
              ? 'text-[#002094] border-b-2 border-[#002094]' 
              : 'text-[#002094]/50 hover:text-[#002094]'
          }`}
          onClick={() => setActiveTab('password')}
        >
          Thay đổi mật khẩu
        </button>
      </div>

      {/* Tab Content: Info */}
      {activeTab === 'info' && (
        <form onSubmit={handleSaveInfo} className="space-y-8 animate-in fade-in duration-300">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
            <div className="md:col-span-2 mb-4">
              <label className="block text-[#002094] text-[13px] font-medium mb-1 opacity-70">
                Số điện thoại
              </label>
              <div className="flex items-center text-[#002094] text-[16px] font-bold bg-[#EFEFEF] px-4 py-3 border-b-2 border-[#002094]/20 cursor-not-allowed">
                <span className="mr-2 border border-gray-300 shadow-sm rounded-sm overflow-hidden flex items-center justify-center w-6 h-4 bg-red-600 relative">
                  <span className="absolute text-yellow-400 text-[10px]">★</span>
                </span>
                +84 {phone.replace(/^0/, '')}
              </div>
            </div>

            <VInput
              label="Họ và tên *"
              value={name}
              onChange={(e) => setName(e.target.value)}
              required
            />
            {/* Vinamilk splits name into Last/Middle and First, we'll keep it simple or split it if needed. 
                For 100% UI match, they have 2 fields. Let's just use 1 field for now as backend has 'name' */}
          </div>

          {/* Banner */}
          <div className="bg-[#E0FB9B] rounded-none p-8 md:p-12 flex flex-col md:flex-row items-center justify-between mt-8 relative overflow-hidden">
            <div className="text-center md:text-left z-10 mb-6 md:mb-0">
              <h3 className="text-[32px] md:text-[40px] font-black text-[#002094] tracking-tighter mb-2">
                Giới thiệu Bạn Mới
              </h3>
              <p className="text-[16px] font-bold text-[#002094] italic font-serif">
                & nhận tới 500.000 điểm
              </p>
            </div>
            <div className="flex flex-col items-end z-10 w-full md:w-auto">
              <div className="bg-white/50 px-6 py-3 rounded-md flex items-center justify-between w-full md:w-auto mb-4 border border-[#002094]/10">
                <span className="text-[#002094] font-bold text-[15px] mr-4">
                  Mã của tôi: {user?.referral_code || 'OJAEXA'}
                </span>
                <button type="button" className="text-[#002094] hover:opacity-70">
                  <Copy size={18} />
                </button>
              </div>
              <VButton type="button" className="w-full md:w-auto px-8 py-3 bg-[#002094] hover:bg-[#001870] text-white">
                Tìm hiểu thêm
              </VButton>
            </div>
          </div>

          <div className="space-y-4 pt-4">
            <VCheckbox
              label={<span className="leading-relaxed">Tôi đồng ý nhận quảng cáo và ưu đãi trong phạm vi sử dụng được quy định tại <Link href="#" className="underline">Chính sách bảo mật</Link></span>}
              checked={agreeAds}
              onChange={(e) => setAgreeAds(e.target.checked)}
            />
            <VCheckbox
              label="Tôi đồng ý tham gia khảo sát người dùng & các nghiên cứu thị trường khác"
              checked={agreeSurvey}
              onChange={(e) => setAgreeSurvey(e.target.checked)}
            />
          </div>

          {infoMessage && (
            <p className="text-[#002094] font-medium">{infoMessage}</p>
          )}

          <div className="pt-4">
            <VButton type="submit" isLoading={isSavingInfo} className="w-auto px-8 py-3">
              Lưu thay đổi
            </VButton>
          </div>
        </form>
      )}

      {/* Tab Content: Password */}
      {activeTab === 'password' && (
        <form onSubmit={handleSavePassword} className="space-y-6 max-w-lg animate-in fade-in duration-300">
          
          {passwordMessage.text && (
            <div className={`p-3 text-sm rounded-md text-center ${passwordMessage.type === 'error' ? 'bg-red-50 text-red-500' : 'bg-green-50 text-green-700'}`}>
              {passwordMessage.text}
            </div>
          )}

          <VInput
            label="Mật khẩu hiện tại *"
            type="password"
            value={currentPassword}
            onChange={(e) => setCurrentPassword(e.target.value)}
            required
          />
          <VInput
            label="Mật khẩu mới *"
            type="password"
            value={newPassword}
            onChange={(e) => setNewPassword(e.target.value)}
            required
            minLength={8}
          />
          <VInput
            label="Xác nhận mật khẩu mới *"
            type="password"
            value={confirmPassword}
            onChange={(e) => setConfirmPassword(e.target.value)}
            required
            minLength={8}
          />

          <div className="pt-4">
            <VButton type="submit" isLoading={isSavingPassword} className="w-auto px-8 py-3">
              Lưu thay đổi
            </VButton>
          </div>
        </form>
      )}
    </div>
  );
}
