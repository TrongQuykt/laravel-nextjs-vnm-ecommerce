'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import VInput from '@/components/ui/VInput';
import VButton from '@/components/ui/VButton';

export default function RecoverPage() {
  const [username, setUsername] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');

  const handleRecover = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);
    setError('');
    setMessage('');

    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000/api/v1'}/forgot-password`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ email: username })
      });
      const data = await res.json();
      
      if (!res.ok) {
        throw new Error(data.message || 'Có lỗi xảy ra, vui lòng thử lại.');
      }
      
      setMessage(data.message || 'Nếu email tồn tại, một mã khôi phục/đường dẫn đã được gửi.');
    } catch (err: any) {
      setError(err.message || 'Đã xảy ra lỗi.');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-[#FDFCF0] flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div className="w-full max-w-md space-y-8">
        <div>
          <h2 className="mt-6 text-center text-[40px] font-black text-[#002094] tracking-tighter">
            Quên mật khẩu?
          </h2>
          <p className="mt-2 text-center text-[15px] font-bold text-[#002094]">
            Vui lòng nhập số điện thoại hoặc email đã đăng ký
          </p>
        </div>
        
        <form className="mt-8 space-y-6" onSubmit={handleRecover}>
          {error && (
            <div className="bg-red-50 text-red-500 p-3 text-sm rounded-md text-center">
              {error}
            </div>
          )}
          {message && (
            <div className="bg-green-50 text-green-700 p-3 text-sm rounded-md text-center">
              {message}
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
          </div>

          <div className="mt-8">
            <VButton type="submit" isLoading={isLoading}>
              Tiếp tục
            </VButton>
          </div>
        </form>

        <div className="mt-10 pt-8 border-t border-[#002094]/10 text-center">
          <p className="text-[15px] font-medium text-[#002094]">
            Quay lại{' '}
            <Link href="/login" className="font-bold underline hover:opacity-80">
              Đăng nhập
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
}
