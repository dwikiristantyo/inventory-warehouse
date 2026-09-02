<x-filament-panels::page>
    <style>
        .profile-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }
        .profile-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid #f3f4f6;
        }
        .avatar-circle {
            width: 3.25rem;
            height: 3.25rem;
            border-radius: 9999px;
            background-color: #18181b;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            font-weight: 700;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1.25rem;
            margin-top: 1.25rem;
        }
        @media (min-width: 640px) {
            .info-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        .info-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 0.375rem;
            letter-spacing: 0.05em;
        }
        .info-value {
            font-size: 0.95rem;
            font-weight: 600;
            color: #111827;
        }
        .chip-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.375rem;
        }
        .chip-item {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.625rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
            background-color: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb;
        }
        .badge-role {
            display: inline-flex;
            padding: 0.25rem 0.625rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 9999px;
            background-color: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
        }
        .btn-signout {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background-color: #ffffff;
            color: #374151;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-signout:hover {
            background-color: #f9fafb;
        }
        .action-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
    </style>

    @php
        $user = auth()->user();
        $userGroup = $user->userGroup->description ?? '-';
        $companies = $user->companies ?? collect();
        $warehouses = $user->warehouses ?? collect();
    @endphp

    <div class="profile-card">
        <!-- Header Welcome & Actions -->
        <div class="profile-header">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div class="avatar-circle">
                    {{ strtoupper(substr($user->username ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <div style="font-size: 1.25rem; font-weight: 700; color: #111827;">Welcome</div>
                    <div style="font-size: 0.95rem; color: #6b7280; font-weight: 500;">
                        {{ $user->username ?? 'User' }}
                    </div>
                </div>
            </div>

            <div class="action-group">
                <!-- Tombol Change Password (Filament Action Modal) -->
                {{ $this->changePasswordAction }}

                <!-- Tombol Logout -->
                <form method="POST" action="{{ route('filament.admin.auth.logout') }}">
                    @csrf
                    <button type="submit" class="btn-signout">
                        <svg style="width: 1.25rem; height: 1.25rem; color: #4b5563;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Sign out
                    </button>
                </form>
            </div>
        </div>

        <!-- Details Grid -->
        <div class="info-grid">
            <div>
                <div class="info-label">NIK</div>
                <div class="info-value">{{ $user->nik ?? '-' }}</div>
            </div>

            <div>
                <div class="info-label">Role</div>
                <div><span class="badge-role">{{ $userGroup }}</span></div>
            </div>

            <div>
                <div class="info-label">Akses Perusahaan</div>
                <div class="chip-container">
                    @forelse($companies as $company)
                        <span class="chip-item">{{ $company->alias ?? $company->company_name }}</span>
                    @empty
                        <span class="info-value" style="color: #9ca3af;">- Tidak ada akses -</span>
                    @endforelse
                </div>
            </div>

            <div>
                <div class="info-label">Akses Gudang</div>
                <div class="chip-container">
                    @forelse($warehouses as $warehouse)
                        <span class="chip-item">{{ $warehouse->warehouse_name }}</span>
                    @empty
                        <span class="info-value" style="color: #9ca3af;">- Tidak ada akses -</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Render Modal untuk Filament Actions -->
    <x-filament-actions::modals />
</x-filament-panels::page>