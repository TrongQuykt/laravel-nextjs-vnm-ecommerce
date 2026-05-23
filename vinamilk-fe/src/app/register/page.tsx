'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import VInput from '@/components/ui/VInput';
import VButton from '@/components/ui/VButton';
import VCheckbox from '@/components/ui/VCheckbox';

export default function RegisterPage() {
  const router = useRouter();
  const [formData, setFormData] = useState({
    name: '',
    phone: '',
    email: '',
    password: '',
    password_confirmation: ''
  });
  const [agreeTerms, setAgreeTerms] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState('');

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleRegister = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!agreeTerms) {
      setError('Vui lòng đồng ý với các điều khoản và chính sách.');
      return;
    }
    if (formData.password !== formData.password_confirmation) {
      setError('Mật khẩu xác nhận không khớp.');
      return;
    }

    setIsLoading(true);
    setError('');

    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000/api/v1'}/register`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          name: formData.name,
          phone: formData.phone,
          email: formData.email,
          password: formData.password
        })
      });

      const data = await res.json();

      if (!res.ok) {
        throw new Error(data.message || (Object.values(data.errors || {}) as string[][])[0]?.[0] || 'Đăng ký thất bại. Vui lòng kiểm tra lại.');
      }

      localStorage.setItem('auth_token', data.access_token);
      localStorage.setItem('user', JSON.stringify(data.user));

      router.push('/account/profile');
    } catch (err: any) {
      setError(err.message);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-[#FDFCF0] flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div className="w-full max-w-md space-y-8">
        <div>
          <h2 className="mt-6 text-center text-[40px] font-black text-[#002094] tracking-tighter">
            Đăng ký
          </h2>
          <p className="mt-2 text-center text-[15px] font-bold text-[#002094]">
            Tạo tài khoản thành viên mới
          </p>
        </div>
        
        <form className="mt-8 space-y-6" onSubmit={handleRegister}>
          {error && (
            <div className="bg-red-50 text-red-500 p-3 text-sm rounded-md text-center">
              {error}
            </div>
          )}
          
          <div className="space-y-4">
            <VInput
              name="name"
              type="text"
              placeholder="Họ và tên *"
              value={formData.name}
              onChange={handleChange}
              required
            />
            <VInput
              name="phone"
              type="tel"
              placeholder="Số điện thoại *"
              value={formData.phone}
              onChange={handleChange}
              required
            />
            <VInput
              name="email"
              type="email"
              placeholder="Email (không bắt buộc)"
              value={formData.email}
              onChange={handleChange}
            />
            <VInput
              name="password"
              type="password"
              placeholder="Nhập mật khẩu *"
              value={formData.password}
              onChange={handleChange}
              required
              minLength={8}
            />
            <VInput
              name="password_confirmation"
              type="password"
              placeholder="Nhập lại mật khẩu *"
              value={formData.password_confirmation}
              onChange={handleChange}
              required
              minLength={8}
            />
          </div>

          <div className="mt-6">
            <VCheckbox
              label={
                <span>
                  Tôi đã đọc và đồng ý với các{' '}
                  <Link href="#" className="font-bold underline hover:opacity-80">điều khoản</Link> và{' '}
                  <Link href="#" className="font-bold underline hover:opacity-80">chính sách bảo mật</Link>
                </span>
              }
              checked={agreeTerms}
              onChange={(e) => setAgreeTerms(e.target.checked)}
            />
          </div>

          <div className="mt-8">
            <VButton type="submit" isLoading={isLoading}>
              Đăng ký
            </VButton>
          </div>
        </form>

        <div className="mt-10 pt-8 border-t border-[#002094]/10 text-center">
          <p className="text-[15px] font-medium text-[#002094]">
            Bạn đã có tài khoản?{' '}
            <Link href="/login" className="font-bold underline hover:opacity-80">
              Đăng nhập
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
}
