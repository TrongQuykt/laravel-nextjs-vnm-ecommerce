'use client';

import React, { useState, useEffect } from 'react';
import { X } from 'lucide-react';
import VInput from '@/components/ui/VInput';
import VSelect from '@/components/ui/VSelect';
import VButton from '@/components/ui/VButton';
import VCheckbox from '@/components/ui/VCheckbox';

interface AddressSidebarProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: () => void;
}

export default function AddressSidebar({ isOpen, onClose, onSuccess }: AddressSidebarProps) {
  const [provinces, setProvinces] = useState<any[]>([]);
  const [districts, setDistricts] = useState<any[]>([]);
  const [wards, setWards] = useState<any[]>([]);

  const [formData, setFormData] = useState({
    last_name: '',
    first_name: '',
    phone: '',
    city: '',
    district: '',
    ward: '',
    detail: '',
    is_default: false,
  });

  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState('');

  // Fetch provinces on mount
  useEffect(() => {
    fetch('https://provinces.open-api.vn/api/?depth=3')
      .then(res => res.json())
      .then(data => setProvinces(data))
      .catch(err => console.error('Error fetching provinces:', err));
  }, []);

  // Update districts when city changes
  useEffect(() => {
    if (formData.city) {
      const selectedProvince = provinces.find(p => p.name === formData.city);
      if (selectedProvince) {
        setDistricts(selectedProvince.districts || []);
        setWards([]);
        setFormData(prev => ({ ...prev, district: '', ward: '' }));
      }
    }
  }, [formData.city, provinces]);

  // Update wards when district changes
  useEffect(() => {
    if (formData.district) {
      const selectedDistrict = districts.find(d => d.name === formData.district);
      if (selectedDistrict) {
        setWards(selectedDistrict.wards || []);
        setFormData(prev => ({ ...prev, ward: '' }));
      }
    }
  }, [formData.district, districts]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleCheckboxChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFormData(prev => ({ ...prev, is_default: e.target.checked }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);
    setError('');

    try {
      const token = localStorage.getItem('auth_token');
      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000/api/v1'}/user/addresses`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify(formData)
      });

      const data = await res.json();
      if (!res.ok) {
        throw new Error(data.message || 'Lỗi khi thêm địa chỉ');
      }

      onSuccess();
      onClose();
      // Reset form
      setFormData({
        last_name: '',
        first_name: '',
        phone: '',
        city: '',
        district: '',
        ward: '',
        detail: '',
        is_default: false,
      });
    } catch (err: any) {
      setError(err.message);
    } finally {
      setIsLoading(false);
    }
  };

  if (!isOpen) return null;

  return (
    <>
      {/* Overlay */}
      <div 
        className="fixed inset-0 bg-black/40 z-[100] transition-opacity" 
        onClick={onClose}
      />
      
      {/* Sidebar */}
      <div className="fixed top-0 right-0 h-full w-[400px] max-w-[90vw] bg-[#FDFCF0] shadow-2xl z-[101] transform transition-transform duration-300 ease-in-out flex flex-col">
        <div className="flex items-center justify-between p-6 border-b border-[#002094]/10">
          <h2 className="text-[18px] font-bold text-[#002094]">Thêm địa chỉ</h2>
          <button onClick={onClose} className="text-[#002094] hover:opacity-70 transition-opacity">
            <X size={24} />
          </button>
        </div>

        <div className="flex-1 overflow-y-auto p-6 scrollbar-thin scrollbar-thumb-[#002094]/20">
          <form id="address-form" onSubmit={handleSubmit} className="space-y-4">
            {error && (
              <div className="bg-red-50 text-red-500 p-3 text-sm rounded-md mb-4">
                {error}
              </div>
            )}

            <VInput
              name="last_name"
              label="Họ và tên đệm *"
              value={formData.last_name}
              onChange={handleChange}
              required
            />
            <VInput
              name="first_name"
              label="Tên *"
              value={formData.first_name}
              onChange={handleChange}
              required
            />
            <VInput
              name="phone"
              label="Số điện thoại người nhận *"
              type="tel"
              value={formData.phone}
              onChange={handleChange}
              required
            />
            
            <VSelect
              name="city"
              label="Thành phố *"
              value={formData.city}
              onChange={handleChange}
              options={provinces.map(p => ({ value: p.name, label: p.name }))}
              required
            />
            
            <VSelect
              name="district"
              label="Quận / Huyện *"
              value={formData.district}
              onChange={handleChange}
              options={districts.map(d => ({ value: d.name, label: d.name }))}
              required
              disabled={!formData.city}
            />
            
            <VSelect
              name="ward"
              label="Phường / Xã *"
              value={formData.ward}
              onChange={handleChange}
              options={wards.map(w => ({ value: w.name, label: w.name }))}
              required
              disabled={!formData.district}
            />

            <VInput
              name="detail"
              label="Địa chỉ *"
              value={formData.detail}
              onChange={handleChange}
              required
            />

            <div className="pt-2">
              <VCheckbox
                label="Đặt làm địa chỉ mặc định"
                checked={formData.is_default}
                onChange={handleCheckboxChange}
              />
            </div>
          </form>
        </div>

        <div className="p-6 border-t border-[#002094]/10 bg-[#FDFCF0]">
          <VButton 
            type="submit" 
            form="address-form" 
            isLoading={isLoading} 
            className="w-full"
          >
            Thêm địa chỉ
          </VButton>
        </div>
      </div>
    </>
  );
}
