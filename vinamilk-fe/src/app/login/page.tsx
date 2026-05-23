'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import VInput from '@/components/ui/VInput';
import VButton from '@/components/ui/VButton';
import VCheckbox from '@/components/ui/VCheckbox';

export default function LoginPage() {
  const router = useRouter();
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [rememberMe, setRememberMe] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState('');

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);
    setError('');

    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000/api/v1'}/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ username, password })
      });

      const data = await res.json();

      if (!res.ok) {
        throw new Error(data.message || data.errors?.username?.[0] || 'Đăng nhập thất bại. Vui lòng kiểm tra lại thông tin.');
      }

      // Save token (in real app, consider secure cookie, but localStorage is quick for now)
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
            Đăng nhập
          </h2>
          <p className="mt-2 text-center text-[15px] font-bold text-[#002094]">
            Đăng nhập vào tài khoản thành viên của bạn
          </p>
        </div>
        
        <form className="mt-8 space-y-6" onSubmit={handleLogin}>
          {error && (
            <div className="bg-red-50 text-red-500 p-3 text-sm rounded-md text-center">
              {error}
            </div>
          )}
          
          <div className="space-y-4">
            <VInput
              type="text"
              placeholder="Số điện thoại / Email *"
              value={username}
              onChange={(e) => setUsername(e.target.value)}
              required
            />
            <VInput
              type="password"
              placeholder="Nhập mật khẩu *"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
            />
          </div>

          <div className="flex items-center justify-between mt-6">
            <VCheckbox
              label="Ghi nhớ đăng nhập"
              checked={rememberMe}
              onChange={(e) => setRememberMe(e.target.checked)}
            />
            <div className="text-[14px]">
              <Link href="/recover" className="font-bold text-[#002094] hover:underline">
                Quên mật khẩu?
              </Link>
            </div>
          </div>

          <div className="mt-8">
            <VButton type="submit" isLoading={isLoading}>
              Đăng nhập
            </VButton>
          </div>
        </form>

        <div className="mt-10 pt-8 border-t border-[#002094]/10 text-center">
          <p className="text-[15px] font-medium text-[#002094]">
            Bạn chưa có tài khoản?{' '}
            <Link href="/register" className="font-bold underline hover:opacity-80">
              Đăng ký
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
}
